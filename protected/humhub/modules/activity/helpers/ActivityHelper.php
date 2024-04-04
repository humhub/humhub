<?php

namespace humhub\modules\activity\helpers;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\PolymorphicRelation;
use humhub\modules\activity\models\Activity;
use Yii;
use yii\db\ActiveQuery;

class ActivityHelper
{
    public static function getActivitiesQuery(?ActiveRecord $record): ?ActiveQuery
    {
        if ($record === null) {
            return null;
        }

        $pk = $record->getPrimaryKey();

        // Check if primary key exists and is not array (multiple pk)
        if ($pk === null || is_array($pk)) {
            return null;
        }

        return Activity::find()->where([
            'object_id' => $pk,
            'object_model' => PolymorphicRelation::getObjectModel($record),
        ]);
    }

    public static function deleteActivitiesForRecord(?ActiveRecord $record): void
    {
        $activitiesQuery = self::getActivitiesQuery($record);

        if ($activitiesQuery === null) {
            return;
        }

        foreach ($activitiesQuery->each() as $activity) {
            /* @var Activity $activity */
            $activity->hardDelete();
        }

        Yii::debug('Deleted activities for ' . get_class($record) . ' with PK ' . $record->getPrimaryKey(), 'activity');
    }

}
