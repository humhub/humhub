<?php

namespace humhub\modules\activity\services;

use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\models\Activity;
use yii\db\Expression;

class GroupingManager
{
    public static function handleInsert(BaseActivity $activity): void
    {
        if ($activity->groupingThreshold && $activity->findGroupedQuery()->count() > $activity->groupingThreshold) {
            $subSelect = $activity->findGroupedQuery()->select('activity.id')->createCommand()->getRawSql();
            Activity::updateAll(
                ['grouping_key' => $activity->record->id . 'grp'],
                new Expression('activity.id IN (' . $subSelect . ')'),
            );

            // Refresh for current activity
            $activity->record->refresh();
        }
    }

    public static function handleDelete(BaseActivity $activity): void
    {
        // Contains Group Count WITHOUT the current activity, because we're running on afterDelete!
        $currentGroupCount = Activity::find()->andWhere(
            ['grouping_key' => $activity->record->grouping_key],
        )->count();

        if ($currentGroupCount === 0) {
            // Record was not grouped
            return;
        }

        if ($activity->groupingThreshold && $currentGroupCount < $activity->groupingThreshold) {
            // We need to remove the current group, because it size is now to small to be grouped
            static::removeGrouping($activity->record->grouping_key);
        }
    }

    public static function handleUpdate(BaseActivity&Activity $activity): void
    {
        // Once the content or content addon_record, was changed - we need to re-check grouping.
        // e.g.
        //    - Content Visibility changed
        //    - Content moved to another container
        //    -
    }


    private static function removeGrouping(string $grouping_key): void
    {
        Activity::updateAll(['grouping_key' => new Expression('id')], ['grouping_key' => $grouping_key]);
    }

}
