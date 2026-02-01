<?php

namespace humhub\modules\activity\services;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\user\models\User;
use yii\db\Expression;

final class GroupingService
{
    private ?array $_groupedUsers = null;

    private bool $groupingEnabled = false;

    private ?ActiveQueryActivity $groupQuery;

    public function __construct(private BaseActivity $activity)
    {
        $this->groupQuery = $this->activity->findGroupedQuery();
        if ($this->groupQuery) {
            $this->groupQuery->timeBucket($this->activity->groupingTimeBucketSeconds, $this->activity->createdAt);
        }
    }

    public function getGroupedUsers(): array
    {
        if (!$this->groupQuery) {
            return [];
        }

        if (!$this->_groupedUsers) {
            $this->_groupedUsers = [];

            $query = $this->activity->findGroupedQuery()
                ->select(['activity.created_by'])->distinct()
                ->defaultScopes($this->activity->user)
                ->andWhere(['!=', 'activity.id', $this->activity->record->id])
                ->limit(3);
            foreach ($query->column() as $userId) {
                $this->_groupedUsers[] = User::findOne($userId);
            }
        }

        return $this->_groupedUsers;
    }

    /**
     * After a new Activity was created OR the grouping of an existing Activity may changed.
     */
    public function afterInsert(): void
    {
        if ($this->needsGrouping()) {
            $subSelect = $this->activity->findGroupedQuery()->select('activity.id')->createCommand()->getRawSql();
            Activity::updateAll(
                ['grouping_key' => $this->activity->record->id],
                // We need a "double" SubSelect to avoid MySQL Err: 1093
                new Expression('activity.id IN (SELECT id FROM (' . $subSelect . ') AS temp_tbl)'),
            );

            // Refresh for current activity
            $this->activity->record->refresh();
        }
    }

    /**
     * After an Activity was deleted
     */
    public function beforeDelete(): void
    {
        // Since we are not afterDelete, the record itself is not counted!
        $currentGroupCount = $this->getGroupCount();

        if ($currentGroupCount <= 1) {
            // Record was not grouped
            return;
        }

        if ($this->activity->groupingThreshold && $currentGroupCount < $this->activity->groupingThreshold) {
            // We need to remove the current group, because it size is now to small to be grouped
            $this->destroyGroup();
        }
    }

    /**
     * After some special updates, like:
     *
     * - Visibility changed
     * - Content moved to another container
     *
     * We may need to upgrade grouping.
     */
    public function afterUpdate(): void
    {
        if (!$this->groupingEnabled) {
            return;
        }

        // Are we currently in a Group?
        /*
        $stillInSameGroup = false;
        if ($this->getGroupCount() > 1) {
            // Check we're still in the group
            if (!$this->getNextGroupedActivity()?->findGroupedQuery()
                ->andWhere(['id' => $this->activity->record->id])->exists()) {
                // We're no longer in the group, another member doesn't find us, via it's grouping query, remove us
                $this->activity->record->grouping_key = $this->activity->record->id;
                $this->activity->record->update(false);
            } else {
                $stillInSameGroup = true;
            }
        }

        // If we were previously removed from a group OR now in a new group, add us:
        if (!$stillInSameGroup) {
            $this->afterInsert();
        }
        */
    }


    private function getGroupCount(): int
    {
        return Activity::find()
            ->andWhere(['grouping_key' => $this->activity->record->grouping_key])
            ->count();
    }

    private function needsGrouping(): bool
    {
        return (
            $this->groupQuery
            && $this->activity->findGroupedQuery()->count() > $this->activity->groupingThreshold
        );
    }

    /**
     * Returns the first next activity in the current Activity group
     */
    private function getNextGroupedActivity(): ?BaseActivity
    {
        $secondActivityRecord = Activity::find()
            ->andWhere(['grouping_key' => $this->activity->record->grouping_key])
            ->andWhere(['!=', 'id', $this->activity->record->id])
            ->orderBy(['created_at DESC', 'id DESC'])->one();
        return ActivityManager::load($secondActivityRecord);
    }

    private function destroyGroup(): void
    {
        Activity::updateAll(
            ['grouping_key' => new Expression('id')],
            ['grouping_key' => $this->activity->record->grouping_key],
        );
    }

}
