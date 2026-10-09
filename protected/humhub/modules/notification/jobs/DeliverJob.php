<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\jobs;

use humhub\modules\notification\components\ActiveQueryNotification;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\models\NotificationDelivery;
use humhub\modules\notification\services\DeliveryScheduler;
use humhub\modules\notification\targets\BaseTarget;
use humhub\modules\notification\targets\DeliveryBatch;
use humhub\modules\queue\ActiveJob;
use humhub\modules\user\models\User;
use humhub\modules\user\services\IsOnlineService;
use Throwable;
use Yii;

/**
 * Sends the pending deliveries of one recipient and channel as one message, pushed by the
 * {@see DeliveryScheduler} (delayed to the `due_at` of the row), by itself as a follow-up and by
 * the {@see \humhub\modules\notification\services\DeliveryService::sweep()}.
 *
 * The job starts only when a pending {@see NotificationDelivery} row of the recipient and channel
 * is due; it then takes all pending rows along, also those not due yet, oldest first, and sorts
 * out, as `skipped`:
 *
 * - all rows when the target is gone or no longer active, or the recipient is no longer enabled;
 * - rows whose notification was seen meanwhile (`seen_at` set), whose class is gone, or for whose
 *   class the target is no longer enabled ({@see BaseTarget::isEnabled()}, also when it throws);
 * - due rows when the target has {@see BaseTarget::$skipWhenOnline} and the recipient was active
 *   on the site within the last minute ({@see IsOnlineService::isRecentlyActive()}) - they see the
 *   web list; rows not yet due stay pending;
 * - rows whose notification no longer loads (e.g. its source is gone).
 *
 * The rest is collapsed by group: rows whose notifications share a `grouping_key` become one
 * entry, loaded as the group's head through {@see ActiveQueryNotification::grouped()} and
 * {@see NotificationManager::load()}, so the message names the group once ("Anna and 3 more
 * liked …"). The entries go to {@see BaseTarget::deliver()} in one {@see DeliveryBatch}; success
 * marks the rows `sent`, an exception marks them `failed` and is logged - no retry.
 *
 * One job per recipient and channel runs at a time (a mutex); a job finding it locked ends. After
 * releasing the lock, every job pushes a follow-up for the earliest pending row left - one written
 * while a job ran (riding along with a row being sent, or whose own job found the lock taken), or
 * one left waiting - see {@see pushFollowUp()}. A job finding nothing due sends nothing, so a
 * doubled or early job is harmless.
 *
 * @internal
 * @since 1.20
 */
class DeliverJob extends ActiveJob
{
    /**
     * Follow-ups a queue that runs jobs inline (e.g. the `Instant` driver) may nest.
     */
    private const MAX_FOLLOW_UP_DEPTH = 5;

    private static int $followUpDepth = 0;

    public int $userId;

    /**
     * @var string the id of the target, e.g. `email`
     */
    public string $channel;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $user = User::findOne(['id' => $this->userId]);
        if ($user === null) {
            // the rows went with the user
            return;
        }

        $mutex = 'notification-deliver-' . $this->userId . '-' . $this->channel;
        if (!Yii::$app->mutex->acquire($mutex)) {
            return;
        }

        try {
            $this->deliverPending($user);
        } finally {
            Yii::$app->mutex->release($mutex);
        }

        $this->pushFollowUp();
    }

    private function deliverPending(User $user): void
    {
        $now = DeliveryScheduler::now();

        $pending = NotificationDelivery::find()->forUserAndChannel($user, $this->channel)->pending();
        if (!(clone $pending)->dueBy($now)->exists()) {
            return;
        }

        $rows = $pending
            ->with('notification')
            ->orderBy(['notification_delivery.due_at' => SORT_ASC, 'notification_delivery.id' => SORT_ASC])
            ->all();
        if ($rows === []) {
            return;
        }

        $target = Yii::$app->notification->getTarget($this->channel);
        if ($target === null || !$target->isActive($user) || (int)$user->status !== User::STATUS_ENABLED) {
            $this->mark($rows, NotificationDelivery::STATE_SKIPPED);
            return;
        }

        $skipped = [];
        $enabled = [];
        $byGroup = [];
        $online = $target->skipWhenOnline && (new IsOnlineService($user))->isRecentlyActive();
        $nowDate = date('Y-m-d H:i:s', $now);
        foreach ($rows as $row) {
            if ($online) {
                // the due ones are seen in the web list; the later ones wait - the user may be gone by then
                if ($row->due_at <= $nowDate) {
                    $skipped[] = $row;
                }
                continue;
            }

            $record = $row->notification;
            if ($record === null || $record->seen_at !== null || !class_exists($record->class)) {
                $skipped[] = $row;
                continue;
            }

            if (!isset($enabled[$record->class])) {
                try {
                    $enabled[$record->class] = $target->isEnabled($record->class, $user);
                } catch (Throwable $e) {
                    Yii::error('Could not check the channel ' . $this->channel . ' for ' . $record->class . ': ' . $e, 'notification');
                    $enabled[$record->class] = false;
                }
            }
            if (!$enabled[$record->class]) {
                $skipped[] = $row;
                continue;
            }

            $byGroup[(int)($record->grouping_key ?? $record->id)][] = $row;
        }

        $notifications = [];
        $deliverable = [];
        foreach ($this->loadGroups($user, $byGroup) as $groupingKey => $notification) {
            if ($notification === null) {
                array_push($skipped, ...$byGroup[$groupingKey]);
                continue;
            }
            $notifications[] = $notification;
            array_push($deliverable, ...$byGroup[$groupingKey]);
        }

        $this->mark($skipped, NotificationDelivery::STATE_SKIPPED);

        if ($notifications === []) {
            return;
        }

        try {
            $target->deliver(new DeliveryBatch($user, $notifications, $this->channel));
        } catch (Throwable $e) {
            Yii::error(
                'Notification #' . implode(', #', array_map(fn(NotificationDelivery $row) => $row->notification_id, $deliverable))
                . ' for user ' . $user->id . ': delivery through ' . $this->channel . ' failed: ' . $e,
                'notification',
            );
            $this->mark($deliverable, NotificationDelivery::STATE_FAILED);
            return;
        }

        $this->mark($deliverable, NotificationDelivery::STATE_SENT, $now);
    }

    /**
     * One notification per group, in the order of the given groups: the group's head with its
     * size, or `null` when it no longer loads (logged).
     *
     * @param array<int, NotificationDelivery[]> $byGroup the rows by grouping key
     * @return array<int, BaseNotification|null> by grouping key
     */
    private function loadGroups(User $user, array $byGroup): array
    {
        if ($byGroup === []) {
            return [];
        }

        $heads = Notification::find()
            ->forUser($user)
            ->grouped()
            ->andWhere(['notification.grouping_key' => array_keys($byGroup)])
            ->indexBy('grouping_key')
            ->all();

        $notifications = [];
        foreach ($byGroup as $groupingKey => $rows) {
            $notifications[$groupingKey] = null;
            try {
                if (isset($heads[$groupingKey])) {
                    $notifications[$groupingKey] = NotificationManager::load($heads[$groupingKey]);
                }
            } catch (Throwable $e) {
                Yii::warning(
                    'Notification #' . implode(', #', array_map(fn(NotificationDelivery $row) => $row->notification_id, $rows))
                    . ' (group ' . $groupingKey . ') for user ' . $user->id . ' is not delivered through ' . $this->channel
                    . ', it does not load: ' . $e,
                    'notification',
                );
            }
        }

        return $notifications;
    }

    /**
     * @param NotificationDelivery[] $rows
     */
    private function mark(array $rows, int $state, ?int $sentAt = null): void
    {
        if ($rows === []) {
            return;
        }

        NotificationDelivery::updateAll(
            ['state' => $state, 'sent_at' => $sentAt !== null ? date('Y-m-d H:i:s', $sentAt) : null],
            ['id' => array_map(fn(NotificationDelivery $row) => $row->id, $rows)],
        );
    }

    /**
     * A job for the earliest pending row left, pushed on every path: a row this job did not take
     * (written while it ran, or whose own job found the lock taken - its id may even be lower, ids
     * commit out of order), or one it left waiting (not due yet while the user was online).
     *
     * With a queue that runs jobs at once (e.g. the `Instant` driver) a follow-up is pushed only
     * for a row due now: it runs inline, finds the row due and takes it, so it cannot loop. A later
     * row on such a queue waits for the next message or the sweep.
     */
    private function pushFollowUp(): void
    {
        $next = NotificationDelivery::find()
            ->forUserAndChannel($this->userId, $this->channel)
            ->pending()
            ->min('notification_delivery.due_at');
        if ($next === null) {
            return;
        }

        $delay = max(0, strtotime($next) - DeliveryScheduler::now());
        if ($delay > 0 && !DeliveryScheduler::queueHonoursDelay()) {
            return;
        }

        if (self::$followUpDepth >= self::MAX_FOLLOW_UP_DEPTH) {
            // only reached with a queue that runs jobs inline; the sweep picks the rows up
            Yii::warning('Follow-up delivery job for user ' . $this->userId . ' through ' . $this->channel . ' not pushed: nested too deep', 'notification');
            return;
        }

        self::$followUpDepth++;
        try {
            Yii::$app->queue
                ->delay($delay)
                ->push(new self(['userId' => $this->userId, 'channel' => $this->channel]));
        } finally {
            self::$followUpDepth--;
        }
    }
}
