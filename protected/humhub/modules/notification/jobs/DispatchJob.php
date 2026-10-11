<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\jobs;

use humhub\components\ActiveRecord;
use humhub\models\RecordMap;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\components\NotificationSet;
use humhub\modules\notification\events\UnreadCountChangedEvent;
use humhub\modules\notification\live\NewNotification;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\services\DeliveryScheduler;
use humhub\modules\notification\services\NotificationListService;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\queue\LongRunningActiveJob;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use Throwable;
use Yii;

/**
 * Writes the notification records of one {@see BaseNotification::send()} call or one
 * {@see NotificationSet} - one per recipient - groups them and hands them to the delivery layer
 * ({@see DeliveryScheduler}), which decides when the mail, push and other channels deliver them.
 *
 * The job processes its {@see $entries} in rank order (a `send()` is a set of one entry). A
 * recipient is notified by the first entry that writes a notification for them, or that they
 * already have (see `dedupe`); every later entry skips them. A recipient an entry skips for any
 * other reason - not enabled, rejected by {@see BaseNotification::canReceive()}, no channel takes
 * the class - may still be notified by a later entry.
 *
 * A record is `listed` (shown in the web list, counted in the badge, sent as a live event) when
 * the web channel is enabled for the recipient and the class ({@see WebTarget::isEnabled()}); a
 * notification only other channels deliver is stored unlisted, and one no channel takes is not
 * stored at all.
 *
 * Only ids travel through the queue: the recipients (or the {@see ActiveQueryUser} selecting
 * them, which the queue serializes as is and which is only resolved when the job runs), the
 * source as a content, container or {@see RecordMap} id and the originator. The records are
 * loaded when the job runs, so a source or originator deleted in the meantime dispatches nothing
 * for that entry, and a queue entry never carries a stale or unserializable object graph.
 *
 * Per entry, a recipient is skipped when they are not enabled, are the originator (unless
 * `notifyOriginator` is set), block or are blocked by the originator, cannot view the
 * content (which covers the content of private spaces), already have a notification of the class
 * about the source by the same originator (unless `dedupe` is `false`), or are
 * rejected by {@see BaseNotification::canReceive()}. A container source alone filters nobody: a
 * user invited to a private space is no member yet. An error for one recipient is logged and does
 * not stop the others.
 *
 * The job is not idempotent: a worker that crashes midway has the job reserved again after its
 * ttr, and a dispatch without a source or with `dedupe` off then writes the rows written so far
 * a second time.
 *
 * @internal
 * @since 1.20
 */
final class DispatchJob extends LongRunningActiveJob
{
    /**
     * @var array<array{class: class-string<BaseNotification>, recipients: ActiveQueryUser|int[], source: array|null, originatorId: int|null, payload: array, notifyOriginator: bool, dedupe: bool}>
     * the entries in rank order, see {@see NotificationManager::dispatchSet()}:
     * - `recipients`: the recipients' query or ids. A query must be self-contained - it is
     *   serialized into the queue, so it cannot be a relation query bound to a `primaryModel`.
     * - `source`: `['content' => id, 'record' => recordMapId]` (`record` for a content addon only),
     *   `['container' => contentcontainerId]`, `['record' => recordMapId]` or `null` without a source
     * - `originatorId`, `payload`, `notifyOriginator`, `dedupe`: see {@see BaseNotification::send()}
     */
    public array $entries = [];

    /**
     * @var array<int, true> the ids of the users notified by an earlier entry
     */
    private array $notified = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->notified = [];
        foreach ($this->entries as $entry) {
            $this->runEntry(array_merge([
                'recipients' => [],
                'source' => null,
                'originatorId' => null,
                'payload' => [],
                'notifyOriginator' => false,
                'dedupe' => true,
            ], $entry));
        }
    }

    private function runEntry(array $entry): void
    {
        $originator = null;
        if ($entry['originatorId'] !== null) {
            $originator = User::findOne(['id' => $entry['originatorId']]);
            if ($originator === null) {
                // the originator is gone
                return;
            }
        }

        $hasSource = $entry['source'] !== null;
        [$content, $container, $sourceRecordId] = $this->resolveSource($entry['source']);
        if ($hasSource && $content === null && $container === null && $sourceRecordId === null) {
            // the source is gone
            return;
        }

        /** @var class-string<BaseNotification> $class */
        $class = $entry['class'];
        $priority = $class::priority();
        $payload = $entry['payload'];
        $notifyOriginator = $entry['notifyOriginator'];
        $dedupe = $entry['dedupe'];
        $contentContainerId = $content !== null ? $content->contentcontainer_id : $container?->contentcontainer_id;

        foreach ($this->recipients($entry['recipients']) as $user) {
            // One failing recipient must not abort the fan-out for the others.
            $record = null;
            $accepted = false;
            try {
                if (isset($this->notified[$user->id])) {
                    // notified by an earlier entry of the set
                    continue;
                }
                if ((int)$user->status !== User::STATUS_ENABLED) {
                    continue;
                }
                if ($originator !== null && !$notifyOriginator && $originator->id === $user->id) {
                    continue;
                }
                if ($originator !== null && ($user->isBlockedForUser($originator) || $originator->isBlockedForUser($user))) {
                    continue;
                }
                if ($content !== null && !$content->canView($user)) {
                    continue;
                }
                if ($dedupe && $hasSource && $this->exists($user, $class, $originator, $content, $contentContainerId, $sourceRecordId)) {
                    // they already have it: that counts as notified
                    $this->notified[$user->id] = true;
                    continue;
                }
                $listed = $this->isListed($class, $user);
                if (!$listed && DeliveryScheduler::getTargets($class, $user) === []) {
                    // no channel takes it: neither the web list nor any other
                    continue;
                }

                $record = new Notification([
                    'class' => $class,
                    'user_id' => $user->id,
                    'originator_id' => $originator?->id,
                    'content_id' => $content?->id,
                    'contentcontainer_id' => $contentContainerId,
                    'source_record_id' => $sourceRecordId,
                    'priority' => $priority->value,
                    'listed' => $listed ? 1 : 0,
                    'payload' => $payload,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                if (!$record->save()) {
                    Yii::error('Could not save notification ' . $class . ' for user ' . $user->id . ': ' . implode(', ', $record->getErrorSummary(true)), 'notification');
                    continue;
                }

                $notification = NotificationManager::fromRecord($record);
                if (!$notification->canReceive($user)) {
                    $record->delete();
                    continue;
                }
                $accepted = true;
                $this->notified[$user->id] = true;

                $notification->getGroupingService()->afterInsert();
                // the delivery is of this record (its id and priority), not of the group head
                $delivered = $notification;
                if ($class::grouping() !== null) {
                    // The record heads its group if it formed or joined one: reload it as the group
                    // row, so the sentence is the grouped one (groupCount is readonly on the object)
                    $head = Notification::find()
                        ->forUser($user)
                        ->grouped()
                        ->andWhere(['notification.grouping_key' => $record->grouping_key])
                        ->one();
                    $notification = $head ? NotificationManager::load($head) : $notification;
                }

                if ($listed) {
                    // A failing live driver (or rendering) must not skip the delivery
                    try {
                        Yii::$app->live->send(new NewNotification([
                            'notificationId' => (int)$record->id,
                            'notificationGroup' => NotificationListService::encodeCursor((int)$record->grouping_key),
                            'contentContainerId' => $user->contentcontainer_id,
                            'ts' => time(),
                            'text' => $notification->asPush(),
                        ]));
                    } catch (Throwable $e) {
                        Yii::error('Live event of notification ' . $class . ' #' . $record->id . ' for user ' . $user->id . ': ' . $e, 'notification');
                    }
                    try {
                        UnreadCountChangedEvent::triggerChanged($user);
                    } catch (Throwable $e) {
                        Yii::error('Unread count event of notification ' . $class . ' #' . $record->id . ' for user ' . $user->id . ': ' . $e, 'notification');
                    }
                }

                $this->deliver($delivered, $user);
            } catch (Throwable $e) {
                Yii::error(
                    'Notification ' . $class . ($record?->id ? ' #' . $record->id : '') . ' for user ' . $user->id . ': ' . $e,
                    'notification',
                );
                if ($record !== null && !$record->isNewRecord && !$accepted) {
                    // written, but not accepted by canReceive(): the recipient must not keep it
                    try {
                        $record->delete();
                    } catch (Throwable $e) {
                        Yii::error('Could not delete notification #' . $record->id . ': ' . $e, 'notification');
                    }
                }
            }
        }
    }

    /**
     * Whether the record appears in the user's web list: the web channel is enabled for the
     * class ({@see WebTarget::isEnabled()}) - else it is stored unlisted, for the other channels.
     */
    private function isListed(string $class, User $user): bool
    {
        foreach (Yii::$app->notification->getTargets($user) as $target) {
            if ($target instanceof WebTarget) {
                return $target->isEnabled($class, $user);
            }
        }

        return false;
    }

    /**
     * Hands the notification to the delivery layer, which schedules its delivery through every
     * channel enabled for the user except the web list (the record itself is that), see
     * {@see DeliveryScheduler}.
     */
    private function deliver(BaseNotification $notification, User $user): void
    {
        (new DeliveryScheduler())->schedule($notification, $user);
    }

    /**
     * Each recipient once.
     *
     * @return iterable<User>
     */
    private function recipients(ActiveQueryUser|array $recipients): iterable
    {
        if ($recipients === []) {
            return;
        }

        $query = $recipients instanceof ActiveQueryUser
            ? $recipients
            : User::find()->where(['user.id' => $recipients]);

        $processed = [];
        foreach ($query->each() as $user) {
            if (isset($processed[$user->id])) {
                continue;
            }
            $processed[$user->id] = true;
            yield $user;
        }
    }

    /**
     * @return array{0: Content|null, 1: ContentContainerActiveRecord|null, 2: int|null}
     */
    private function resolveSource(?array $source): array
    {
        if ($source === null) {
            return [null, null, null];
        }

        $content = null;
        $container = null;
        $sourceRecordId = null;

        if (isset($source['content'])) {
            $content = Content::findOne(['id' => $source['content']]);
            if ($content === null) {
                return [null, null, null];
            }
        }

        if (isset($source['container'])) {
            $container = ContentContainer::findOne(['id' => $source['container']])?->polymorphicRelation;
            if (!$container instanceof ContentContainerActiveRecord) {
                return [null, null, null];
            }
        }

        if (isset($source['record'])) {
            if (RecordMap::getById((int)$source['record'], ActiveRecord::class, false) === null) {
                return [null, null, null];
            }
            $sourceRecordId = (int)$source['record'];
        }

        return [$content, $container, $sourceRecordId];
    }

    private function exists(User $user, string $class, ?User $originator, ?Content $content, ?int $contentContainerId, ?int $sourceRecordId): bool
    {
        return Notification::find()
            ->andWhere([
                'notification.user_id' => $user->id,
                'notification.class' => $class,
                'notification.originator_id' => $originator?->id,
                'notification.content_id' => $content?->id,
                'notification.contentcontainer_id' => $contentContainerId,
                'notification.source_record_id' => $sourceRecordId,
            ])
            ->exists();
    }
}
