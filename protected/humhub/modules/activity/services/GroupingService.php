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

    private ?BaseActivity $_sibling = null;

    public ?ActiveQueryActivity $groupQuery;

    public function __construct(private BaseActivity $activity)
    {
        $this->groupQuery = $this->activity->getGroupingQuery();
        if ($this->groupQuery) {
            $this->groupQuery->timeBucket($this->activity->groupingTimeBucketSeconds, $this->activity->createdAt);
        }
    }

    public function getOtherGroupedUsers(?User $currentUser = null): array
    {
        if (!$this->groupQuery) {
            return [];
        }

        if (!$this->_groupedUsers) {
            $this->_groupedUsers = User::find()->visible()
                ->leftJoin('activity', 'user.id=activity.created_by')
                ->andWhere(['activity.grouping_key' => $this->activity->record->grouping_key])
                ->andWhere(['!=', 'activity.id', $this->activity->record->id])
                ->andWhere(['!=', 'activity.created_by', $currentUser->id ?? 0])
                ->orderBy('activity.id DESC')
                ->limit(5)
                ->all();
        }

        return $this->_groupedUsers;
    }

    /**
     * After a new Activity was created OR the grouping of an existing Activity may changed.
     */
    public function afterInsert(): void
    {
        if ($this->needsGrouping()) {
            $subSelect = (clone $this->groupQuery)->select('activity.id')->createCommand()->getRawSql();
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
        if (!$this->groupQuery) {
            return;
        }

        // We're not in a group, check for grouping
        if ($this->getGroupCount() <= 1) {
            $this->afterInsert();
            return;
        }

        // No longer in assigned group
        if (!$this->checkStillInCurrentGroup()) {
            $sibling = $this->getSiblingActivity();

            $this->activity->record->updateAttributes(['grouping_key' => $this->activity->record->id]);

            // Check if old group is large enough
            if (!$sibling->getGroupingService()->needsGrouping()) {
                $sibling->getGroupingService()->destroyGroup();
            }

            $this->afterInsert();
        }
    }

    /**
     * Checks if the current activity is still in the current group.
     * Query another group member and check if we're still a sibiling.
     *
     * @return bool
     */
    private function checkStillInCurrentGroup(): bool
    {
        return $this->getSiblingActivity()->getGroupingService()->hasSibling($this->activity->record->id) ?? false;
    }

    public function hasSibling(int $id): bool
    {
        $query = clone $this->groupQuery;
        return $query->andWhere(['activity.id' => $id])->exists();
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
            && $this->groupQuery->count() >= $this->activity->groupingThreshold
        );
    }

    private function getSiblingActivity(): ?BaseActivity
    {
        if (!$this->_sibling) {
            // Returns the first next activity in the current Activity group
            $record = Activity::find()
                ->andWhere(['grouping_key' => $this->activity->record->grouping_key])
                ->andWhere(['!=', 'activity.id', $this->activity->record->id])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])->one();
            if ($record) {
                $this->_sibling = ActivityManager::load($record);
            }
        }

        return $this->_sibling;
    }

    private function destroyGroup(): void
    {
        Activity::updateAll(
            ['grouping_key' => new Expression('id')],
            ['grouping_key' => $this->activity->record->grouping_key],
        );
    }

}
