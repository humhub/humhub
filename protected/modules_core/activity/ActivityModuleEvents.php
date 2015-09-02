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

/**
 * ActivityModuleEvents
 * Handles registered events of ActivityModule
 * 
 * @package humhub.modules_core.activity
 * @author luke
 * @since 0.11
 */
class ActivityModuleEvents
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

        $model = get_class($event->sender);
        $pk = $event->sender->getPrimaryKey();

        // Check if primary key exists and is not array (multiple pk) 
        if ($pk !== null && !is_array($pk)) {
            foreach (Activity::model()->findAllByAttributes(array('object_id' => $pk, 'object_model' => $model)) as $activity) {
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
        $integrityChecker->showTestHeadline("Validating Activity Module (" . Activity::model()->count() . " entries)");

        // Loop over all comments
        foreach (Activity::model()->findAll() as $a) {

            if ($a->object_model != "" && $a->object_id != "" && $a->getUnderlyingObject() === null) {
                $integrityChecker->showFix("Deleting activity id " . $a->id . " without existing target!");
                if (!$integrityChecker->simulate)
                    $a->delete();
            }

            $content = Content::model()->findByAttributes(array('object_model' => 'Activity', 'object_id' => $a->id));
            if ($content === null) {
                $integrityChecker->showFix("Deleting activity id " . $a->id . " without corresponding content record!");
                if (!$integrityChecker->simulate)
                    $a->delete();
            }
        }
    }

}
