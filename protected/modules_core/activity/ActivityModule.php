<?php

/**
 * ActivityModule is responsible for all activities functions.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.modules_core.activity
 * @since 0.5
 */
class ActivityModule extends CWebModule {

    /**
     * Inits the activity module
     */
    public function init() {
        $this->setImport(array(
            'activity.models.*',
            'activity.behaviors.*',
        ));
    }

    /**
     * On User delete, also delete all activities
     *
     * @param type $event
     */
    public static function onUserDelete($event) {

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
    public static function onActiveRecordDelete($event) {

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
    public static function onSpaceDelete($event) {

        foreach (Content::model()->findAllByAttributes(array('space_id' => $event->sender->id, 'object_model' => 'Activity')) as $content) {
            $content->delete();
        }
    }

    /**
     * On run of integrity check command, validate all module data
     *
     * @param CEvent $event
     */
    public static function onIntegrityCheck($event) {

        $integrityChecker = $event->sender;
        $integrityChecker->showTestHeadline("Validating Activity Module (" . Activity::model()->count() . " entries)");

        // Loop over all comments
        foreach (Activity::model()->findAll() as $a) {

            if ($a->object_model != "" && $a->object_id != "" && $a->getUnderlyingObject() === null) {
                $integrityChecker->showFix("Deleting activity id " . $a->id . " without existing target!");
                if (!$integrityChecker->simulate)
                    $a->delete();
            }
        }
    }

    /**
     * Formatted the activity content before delivery
     *
     * @param string $text
     */
    public static function formatOutput($text) {
        $text = HHtml::translateUserMentioning($text, false);
        return $text;
    }

}
