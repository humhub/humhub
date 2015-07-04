<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

namespace humhub\modules\activity;

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

        // Deleted all activities by the user
        foreach (Content::model()->findAllByAttributes(array('created_by' => $user->id, 'object_model' => 'Activity')) as $content) {
            $content->delete();
        }
        foreach (Content::model()->findAllByAttributes(array('user_id' => $user->id, 'object_model' => 'Activity')) as $content) {
            $content->delete();
        }

        // Try to find activities about this user
        foreach (Activity::model()->findAllByAttributes(array('object_model' => 'User', 'object_id' => $user->id)) as $userActivity) {
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

            if ($a->object_model != "" && $a->object_id != "" && $a->getUnderlyingObject() === null) {
                if ($integrityChecker->showFix("Deleting activity id " . $a->id . " without existing target! (".$a->object_model.")")) {
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
