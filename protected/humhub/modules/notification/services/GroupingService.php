<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\services;

use humhub\modules\notification\components\ActiveQueryNotification;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\Grouping;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\user\models\User;
use yii\db\Expression;

/**
 * Grouping of a notification with the recipient's other notifications matched by the
 * {@see Grouping} of its class ({@see BaseNotification::grouping()}).
 *
 * The group query of a notification: the recipient's notifications of the same class, created in
 * the same time bucket, matching it in the columns of the grouping (and unseen, for
 * {@see Grouping::unseenOnly()}). The members of a group share the `grouping_key` - the id of the
 * group head, its newest member.
 *
 * @internal
 * @since 1.20
 */
final class GroupingService
{
    /**
     * The columns a {@see Grouping} matches - `notification.content_id`, `notification.source_record_id`,
     * `notification.contentcontainer_id`, `notification.originator_id` and the `object_model` of the content.
     */
    public const CONTENT = 'content';
    public const SOURCE = 'source';
    public const CONTAINER = 'container';
    public const ORIGINATOR = 'originator';
    public const CONTENT_TYPE = 'contentType';

    private const COLUMNS = [
        self::CONTENT => 'content_id',
        self::SOURCE => 'source_record_id',
        self::CONTAINER => 'contentcontainer_id',
        self::ORIGINATOR => 'originator_id',
    ];

    private readonly ?Grouping $grouping;

    private ?array $_groupedUsers = null;

    private ?int $_groupedUsersCount = null;

    private ?BaseNotification $_sibling = null;

    /**
     * @var ActiveQueryNotification|null the recipient's notifications in the time bucket of this one
     * that it is grouped with; `null` for an ungrouped notification
     */
    public ?ActiveQueryNotification $groupQuery;

    public function __construct(private readonly BaseNotification $notification)
    {
        $this->grouping = $notification::grouping();
        $this->groupQuery = $this->buildGroupQuery();
    }

    /**
     * The query of the notifications this one is grouped with; `null` when it is not grouped: its
     * class has no grouping, or the notification lacks a column the grouping requires.
     */
    private function buildGroupQuery(): ?ActiveQueryNotification
    {
        if ($this->grouping === null) {
            return null;
        }

        $record = $this->notification->record;
        $query = Notification::find()
            ->andWhere(['notification.class' => $record->class])
            ->forUser($this->notification->recipient)
            ->timeBucket($this->grouping->getTimeBucket(), $record->created_at);

        foreach ($this->grouping->getRequired() as $name) {
            $value = $record->getAttribute(self::COLUMNS[$name]);
            if ($value === null) {
                return null;
            }
            $query->andWhere(['notification.' . self::COLUMNS[$name] => $value]);
        }

        foreach ($this->grouping->getMatched() as $name) {
            if ($name === self::CONTENT_TYPE) {
                $objectModel = $this->notification->content?->object_model;
                if ($objectModel === null) {
                    return null;
                }
                $query->select('notification.*')
                    ->innerJoin('content', 'content.id = notification.content_id')
                    ->andWhere(['content.object_model' => $objectModel]);
                continue;
            }
            $query->andWhere(['notification.' . self::COLUMNS[$name] => $record->getAttribute(self::COLUMNS[$name])]);
        }

        if ($this->grouping->isUnseenOnly()) {
            $query->andWhere(['notification.seen_at' => null]);
        }

        return $query;
    }

    /**
     * The other originators of the group - never the notification's own originator, never the
     * recipient - newest first, at most 5.
     *
     * @return User[]
     */
    public function getOtherGroupedUsers(): array
    {
        if ($this->groupQuery === null) {
            return [];
        }

        if ($this->_groupedUsers === null) {
            $this->_groupedUsers = User::find()->visible($this->notification->recipient)
                ->leftJoin('notification', 'user.id = notification.originator_id')
                ->andWhere(['notification.grouping_key' => $this->notification->record->grouping_key])
                ->andWhere(['notification.user_id' => $this->notification->recipient->id])
                // The notification's own originator is named separately by the sentence
                // (the `displayNames` message parameter), so they are not one of the "others".
                ->andWhere(['!=', 'notification.originator_id', $this->notification->originator?->id ?? 0])
                ->andWhere(['!=', 'notification.originator_id', $this->notification->recipient->id])
                // One row per person: the sentence counts people ("and 3 more"), not notifications.
                ->groupBy('user.id')
                ->orderBy([new Expression('MAX(notification.id) DESC')])
                ->limit(5)
                ->all();
        }

        return $this->_groupedUsers;
    }

    /**
     * The number of the other originators of the group - same exclusions as
     * {@see getOtherGroupedUsers()}, but neither limited nor filtered by visibility.
     */
    public function countOtherGroupedUsers(): int
    {
        if ($this->groupQuery === null) {
            return 0;
        }

        return $this->_groupedUsersCount ??= (int)Notification::find()
            ->select(new Expression('COUNT(DISTINCT notification.originator_id)'))
            ->andWhere(['notification.grouping_key' => $this->notification->record->grouping_key])
            ->andWhere(['notification.user_id' => $this->notification->recipient->id])
            ->andWhere(['IS NOT', 'notification.originator_id', null])
            ->andWhere(['!=', 'notification.originator_id', $this->notification->originator?->id ?? 0])
            ->andWhere(['!=', 'notification.originator_id', $this->notification->recipient->id])
            ->scalar();
    }

    /**
     * After a new notification was created, or when the grouping of an existing one may have
     * changed: groups the recipient's notifications matched by the grouping query once they reach
     * the threshold. The notification becomes the group head, so the entry moves to the top.
     *
     * Two inserts of the same bucket racing can leave a split group, which the next insert
     * merges - there is no lock on purpose.
     */
    public function afterInsert(): void
    {
        if ($this->needsGrouping()) {
            $subSelect = (clone $this->groupQuery)->select('notification.id')->createCommand()->getRawSql();
            Notification::updateAll(
                ['grouping_key' => $this->notification->record->id],
                // We need a "double" SubSelect to avoid MySQL Err: 1093
                new Expression('notification.id IN (SELECT id FROM (' . $subSelect . ') AS temp_tbl)'),
            );

            $this->notification->record->refresh();
        }

        $this->resetCaches();
    }

    /**
     * Before the notification is deleted: destroys its group when it falls below the threshold,
     * otherwise re-keys the remaining members to the newest of them when the head is deleted.
     */
    public function beforeDelete(): void
    {
        $this->leaveGroup();
    }

    /**
     * After an update that may change the grouping (e.g. the content moved): a notification not in
     * a group is grouped if possible, one that no longer belongs to its group leaves it and is
     * grouped anew.
     */
    public function afterUpdate(): void
    {
        if ($this->groupQuery === null) {
            return;
        }

        // We're not in a group, check for grouping
        if ($this->getGroupCount() <= 1) {
            $this->afterInsert();
            return;
        }

        // No longer in the assigned group
        if (!$this->checkStillInCurrentGroup()) {
            $sibling = $this->getSibling();

            // The remaining members must not keep the key of a head that left
            $this->leaveGroup();
            $this->notification->record->updateAttributes(['grouping_key' => $this->notification->record->id]);

            // Semantic re-check of the old group after leaveGroup(): is it still matched often enough?
            // Without a sibling the group shrank concurrently, nothing is left to check.
            if ($sibling !== null && !$sibling->getGroupingService()->needsGrouping()) {
                $sibling->record->refresh();
                $sibling->getGroupingService()->destroyGroup();
            }

            $this->afterInsert();
        }
    }

    /**
     * Whether the notification with the given id is matched by this notification's grouping query.
     */
    private function hasSibling(int $id): bool
    {
        return $this->groupQuery !== null
            && (clone $this->groupQuery)->andWhere(['notification.id' => $id])->exists();
    }

    /**
     * Detaches the other members of the group from this notification: the group is destroyed
     * when the others fall below the threshold, otherwise they are re-keyed to the newest of them
     * if this notification is the head. This notification's own key is left unchanged.
     */
    private function leaveGroup(): void
    {
        $record = $this->notification->record;
        $othersCondition = [
            'and',
            ['user_id' => $record->user_id, 'grouping_key' => $record->grouping_key],
            ['!=', 'id', $record->id],
        ];
        $others = Notification::find()->andWhere($othersCondition);

        $count = (int)(clone $others)->count();
        if ($count === 0) {
            return;
        }

        if ($count < $this->getThreshold()) {
            Notification::updateAll(['grouping_key' => new Expression('id')], $othersCondition);
        } elseif ((int)$record->grouping_key === (int)$record->id) {
            Notification::updateAll(['grouping_key' => (int)(clone $others)->max('id')], $othersCondition);
        }

        $this->resetCaches();
    }

    /**
     * Checks through another member of the group whether this notification still belongs to it.
     */
    private function checkStillInCurrentGroup(): bool
    {
        return $this->getSibling()?->getGroupingService()->hasSibling($this->notification->record->id) ?? false;
    }

    private function getGroupCount(): int
    {
        return (int)Notification::find()
            ->andWhere($this->groupCondition())
            ->count();
    }

    private function needsGrouping(): bool
    {
        return $this->groupQuery !== null
            && $this->groupQuery->count() >= $this->getThreshold();
    }

    /**
     * The newest (highest id, like the head) other member of the group.
     */
    private function getSibling(): ?BaseNotification
    {
        if ($this->_sibling === null) {
            $record = Notification::find()
                ->andWhere($this->groupCondition())
                ->andWhere(['!=', 'notification.id', $this->notification->record->id])
                ->orderBy(['id' => SORT_DESC])
                ->one();
            if ($record !== null) {
                $this->_sibling = NotificationManager::load($record);
            }
        }

        return $this->_sibling;
    }

    private function destroyGroup(): void
    {
        Notification::updateAll(['grouping_key' => new Expression('id')], $this->groupCondition());
    }

    /**
     * The members of this notification's group; with `user_id` for the `(user_id, grouping_key)` index.
     */
    private function groupCondition(): array
    {
        return [
            'notification.user_id' => $this->notification->record->user_id,
            'notification.grouping_key' => $this->notification->record->grouping_key,
        ];
    }

    private function getThreshold(): int
    {
        return $this->grouping?->getThreshold() ?? 2;
    }

    private function resetCaches(): void
    {
        $this->_sibling = null;
        $this->_groupedUsers = null;
        $this->_groupedUsersCount = null;
    }
}
