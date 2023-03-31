<?php

namespace humhub\modules\activity\helpers;

use humhub\components\ActiveRecord;
use humhub\modules\activity\models\Activity;
use Yii;

class ActivityHelper
{

    public static function deleteActivitiesForRecord(?ActiveRecord $record)
    {
        if ($record === null) {
            return;
        }

        $pk = $record->getPrimaryKey();

        // Check if primary key exists and is not array (multiple pk)
        if ($pk !== null && !is_array($pk)) {

            $modelsActivity = Activity::find()->where([
                'object_id' => $pk,
                'object_model' => get_class($record)
            ])->each();

            foreach ($modelsActivity as $activity) {
                /* @var Activity $activity */
                $activity->hardDelete();
            }

            Yii::debug('Deleted activities for ' . get_class($record) . " with PK " . $pk, 'activity');
        }
    }

}
