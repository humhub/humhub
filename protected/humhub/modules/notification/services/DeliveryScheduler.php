<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\services;

use Closure;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\jobs\DeliverJob;
use humhub\modules\notification\models\NotificationDelivery;
use humhub\modules\queue\driver\Instant;
use humhub\modules\notification\targets\BaseTarget;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\user\models\User;
use Throwable;
use Yii;
use yii\db\Expression;

/**
 * Decides when a notification goes out through each channel of its recipient, called by the
 * {@see \humhub\modules\notification\jobs\DispatchJob} for every notification it writes.
 *
 * For every target active for the recipient and enabled for the notification's class
 * ({@see BaseTarget::isEnabled()}), except the web list, one {@see NotificationDelivery} row is
 * written with its `due_at`:
 *
 * - `high` priority: now;
 * - otherwise the delay is `delays[min(n, count(delays) - 1)]` of the target, where `n` is the
 *   number of messages the channel sent the recipient within the target's `delayWindow`
 *   (counted as distinct `sent_at` of the sent rows, since the rows of one batch share it): the
 *   first message in a quiet hour goes at once, every further one waits longer;
 * - `low` priority waits at least the target's `lowPriorityDelay`.
 *
 * Whenever a message goes out, the {@see DeliverJob} takes all pending rows of the recipient and
 * channel along, also those not due yet - so a low-priority notification usually rides along with
 * the next message rather than waiting its full delay.
 *
 * If the recipient already has a pending delivery of the channel that is due at least
 * {@see CARRIER_MIN_LEAD} seconds from now and no later than the computed time, the new row takes
 * over its `due_at` and no job is pushed: it rides along in that message. A pending row due
 * sooner (or overdue) is no carrier - its job may be running or lost - so the new row then gets a
 * job of its own. Otherwise a {@see DeliverJob} for the recipient and channel is pushed, delayed
 * to `due_at`. A target with `delays = [0]` and a `high` notification are effectively instant.
 *
 * A failing channel is logged and does not stop the others.
 *
 * ## Queues without delay
 *
 * Only a queue that honours the delay of a job can deliver later. With any other queue (the
 * `Instant` and `Sync` drivers, or an unknown one) the scheduler treats every delay as 0, so each
 * notification goes out at once, one message each - see {@see isInstant()}; the `instantDelivery`
 * of the `notification` component overrides the detection, e.g. for a custom queue driver.
 *
 * ## Clock
 *
 * {@see now()} is the time all of the delivery layer works with; tests set {@see $clock} to
 * control it instead of sleeping. `due_at`, `sent_at` and `created_at` are local wall-clock
 * datetimes (`date('Y-m-d H:i:s')`) as everywhere in HumHub: across a DST change a delay may be
 * an hour shorter or longer, which the sweep and the next message absorb.
 *
 * @internal
 * @since 1.20
 */
class DeliveryScheduler
{
    /**
     * @var Closure|null returns the current unix timestamp; `null` for `time()`. A seam for tests.
     */
    public static ?Closure $clock = null;

    /**
     * Seconds a pending row must still be away to carry a new one, see the class description.
     */
    public const CARRIER_MIN_LEAD = 5;

    /**
     * Queue classes known to honour the delay of a job; checked by name, so a driver that is not
     * installed costs nothing.
     */
    private const DELAYING_QUEUES = [
        'yii\queue\db\Queue',
        'yii\queue\redis\Queue',
        'yii\queue\file\Queue',
        'yii\queue\beanstalk\Queue',
        'yii\queue\amqp_interop\Queue',
    ];

    /**
     * Whether deliveries go out at once, every delay treated as 0: the `instantDelivery` of the
     * `notification` component if set
     * ({@see \humhub\modules\notification\components\NotificationManager::$instantDelivery}), else
     * whether the queue does not honour a job's delay ({@see queueHonoursDelay()}).
     */
    public static function isInstant(): bool
    {
        return Yii::$app->notification->instantDelivery ?? !self::queueHonoursDelay();
    }

    /**
     * Whether `Yii::$app->queue` is known to run a job no earlier than its delay - not e.g. the
     * {@see Instant} and `Sync` drivers, which run it at once.
     */
    public static function queueHonoursDelay(): bool
    {
        $queue = Yii::$app->queue;
        foreach (self::DELAYING_QUEUES as $class) {
            if ($queue instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * The current unix timestamp of the delivery layer, see {@see $clock}.
     */
    public static function now(): int
    {
        return self::$clock !== null ? (int)(self::$clock)() : time();
    }

    /**
     * Schedules the deliveries of the notification to the user through all their channels.
     */
    public function schedule(BaseNotification $notification, User $user): void
    {
        foreach (self::getTargets($notification::class, $user) as $target) {
            try {
                $this->scheduleTarget($notification, $user, $target);
            } catch (Throwable $e) {
                Yii::error('Notification #' . $notification->record->id . ' delivery through ' . $target->id . ' failed: ' . $e, 'notification');
            }
        }
    }

    /**
     * The channels other than the web list that deliver notifications of the class to the user
     * ({@see BaseTarget::isEnabled()}). A channel failing to decide is logged and left out.
     *
     * @param class-string<BaseNotification> $notificationClass
     * @return BaseTarget[]
     */
    public static function getTargets(string $notificationClass, User $user): array
    {
        $targets = [];
        foreach (Yii::$app->notification->getTargets($user) as $target) {
            if ($target instanceof WebTarget) {
                continue;
            }

            try {
                if ($target->isEnabled($notificationClass, $user)) {
                    $targets[] = $target;
                }
            } catch (Throwable $e) {
                Yii::error('Notification ' . $notificationClass . ' for user ' . $user->id . ' through ' . $target->id . ': ' . $e, 'notification');
            }
        }

        return $targets;
    }

    private function scheduleTarget(BaseNotification $notification, User $user, BaseTarget $target): void
    {
        $now = self::now();
        $due = self::isInstant() ? $now : $now + $this->delay($target, NotificationPriority::tryFrom((int)$notification->record->priority) ?? NotificationPriority::Normal, $user, $now);

        // Ride along with a pending message that goes out no later than this one would
        $carrier = NotificationDelivery::find()
            ->forUserAndChannel($user, $target->id)
            ->pending()
            ->andWhere(['>=', 'notification_delivery.due_at', date('Y-m-d H:i:s', $now + self::CARRIER_MIN_LEAD)])
            ->dueBy($due)
            ->min('notification_delivery.due_at');

        $delivery = new NotificationDelivery([
            'notification_id' => $notification->record->id,
            'user_id' => $user->id,
            'channel' => $target->id,
            'state' => NotificationDelivery::STATE_PENDING,
            'due_at' => $carrier ?? date('Y-m-d H:i:s', $due),
            'created_at' => date('Y-m-d H:i:s', $now),
        ]);
        if (!$delivery->save()) {
            Yii::error('Could not save the delivery of notification #' . $notification->record->id . ' through ' . $target->id . ': ' . implode(', ', $delivery->getErrorSummary(true)), 'notification');
            return;
        }

        if ($carrier === null) {
            Yii::$app->queue
                ->delay(max(0, $due - $now))
                ->push(new DeliverJob(['userId' => (int)$user->id, 'channel' => $target->id]));
        }
    }

    /**
     * Seconds the delivery waits, see the class description.
     */
    private function delay(BaseTarget $target, NotificationPriority $priority, User $user, int $now): int
    {
        if ($priority === NotificationPriority::High) {
            return 0;
        }

        $delays = array_values($target->delays) ?: [0];
        $sent = (int)NotificationDelivery::find()
            ->forUserAndChannel($user, $target->id)
            ->andWhere(['notification_delivery.state' => NotificationDelivery::STATE_SENT])
            ->andWhere(['>=', 'notification_delivery.sent_at', date('Y-m-d H:i:s', $now - $target->delayWindow)])
            ->select(new Expression('COUNT(DISTINCT notification_delivery.sent_at)'))
            ->scalar();
        $delay = (int)$delays[min($sent, count($delays) - 1)];

        if ($priority === NotificationPriority::Low) {
            $delay = max($delay, $target->lowPriorityDelay);
        }

        return max(0, $delay);
    }
}
