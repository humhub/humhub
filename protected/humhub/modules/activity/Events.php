<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity;

use humhub\modules\user\models\User;
use humhub\modules\activity\models\Activity;

/**
 * ActivityModuleEvents
 * Handles registered events of ActivityModule
 *
 * @package humhub.modules_core.activity
 * @author luke
 * @since 0.11
 */
class Events extends \yii\base\Object
{

    /**
     * On User delete, also delete all activities
     *
     * @param type $event
     */
    public static function onUserDelete($event)
    {

        $user = $event->sender;

        foreach (Activity::findAll(array('object_model' => User::className(), 'object_id' => $user->id)) as $userActivity) {
            $userActivity->delete();
        }

        return true;
    }

    /**
     * On delete of some active record, check if there are related activities and delete them.
     */
    public static function onActiveRecordDelete($event)
    {

        $model = $event->sender->className();
        $pk = $event->sender->getPrimaryKey();

        // Check if primary key exists and is not array (multiple pk)
        if ($pk !== null && !is_array($pk)) {
            foreach (models\Activity::find()->where(['object_id' => $pk, 'object_model' => $model])->all() as $activity) {
                $activity->delete();
            }
        }
    }

    /**
     * On workspace deletion make sure to delete all activities
     *
     * @param type $event
     */
    public static function onSpaceDelete($event)
    {

        foreach (Content::model()->findAllByAttributes(array('space_id' => $event->sender->id, 'object_model' => 'Activity')) as $content) {
            $content->delete();
        }
    }

    /**
     * On run of integrity check command, validate all module data
     *
     * @param CEvent $event
     */
    public static function onIntegrityCheck($event)
    {

        $integrityChecker = $event->sender;
        $integrityChecker->showTestHeadline("Activity Module (" . Activity::find()->count() . " entries)");

        // Loop over all comments
        foreach (Activity::find()->all() as $a) {

            if ($a->object_model != "" && $a->object_id != "" && $a->getSource() === null) {
                if ($integrityChecker->showFix("Deleting activity id " . $a->id . " without existing target! (" . $a->object_model . ")")) {
                    $a->delete();
                }
            }

            $content = \humhub\modules\content\models\Content::findOne(['object_model' => Activity::className(), 'object_id' => $a->id]);
            if ($content === null) {
                if ($integrityChecker->showFix("Deleting activity id " . $a->id . " without corresponding content record!")) {
                    $a->delete();
                }
            }
        }
    }

}
