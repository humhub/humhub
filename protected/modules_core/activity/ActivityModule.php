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
     * On delete of a content object, also try delete all corresponding activities
     */
    public static function onContentDelete($event) {

        foreach (Activity::model()->findAllByAttributes(array('object_id' => $event->sender->id, 'object_model' => get_class($event->sender))) as $activity) {
            $activity->delete();
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
     * On run of integrity check command, validate all activity
     *
     * @param type $event
     */
    public static function onIntegrityCheck($event) {

        $integrityChecker = $event->sender;

        $integrityChecker->showTestHeadline("Validating Activity Module (" . Activity::model()->count() . " entries)");

        foreach (Activity::model()->findAll() as $a) {

            // Check if underlying Model Exists
            $className = $a->object_model;
            if ($className != "") {
                $obj = $className::model()->findByPk($a->object_id);
                if ($obj === null) {
                    $integrityChecker->showFix("Deleting activity with id " . $a->id . " without existing underlying object!");
                    if (!$integrityChecker->simulate)
                        $a->delete();
                    continue;
                }
            }
            if ($a->contentMeta != null) {
                if ($a->contentMeta->getUser() == null) {
                    $integrityChecker->showFix("Deleting activity with id " . $a->id . " without existing user!");
                    if (!$integrityChecker->simulate)
                        $a->delete();
                    continue;
                }
            }
        }
    }

}