<?php

namespace humhub\core\like;

/**
 * This module provides like support for Content and Content Addons
 * Each wall entry will get a Like Button and a overview of likes.
 *
 * @package humhub.modules_core.like
 * @since 0.5
 */
class Module extends \yii\base\Module
{

    public $isCoreModule = true;

    /**
     * On User delete, also delete all comments
     *
     * @param type $event
     */
    public static function onUserDelete($event)
    {

        foreach (Like::model()->findAllByAttributes(array('created_by' => $event->sender->id)) as $like) {
            $like->delete();
        }

        return true;
    }

    /**
     * On delete of a content object, also delete all corresponding likes
     */
    public static function onContentDelete($event)
    {

        foreach (Like::model()->findAllByAttributes(array('object_id' => $event->sender->id, 'object_model' => get_class($event->sender))) as $like) {
            $like->delete();
        }
    }

    /**
     * On delete of a content addon object, e.g. a comment
     * also delete all likes
     */
    public static function onContentAddonDelete($event)
    {

        foreach (Like::model()->findAllByAttributes(array('object_id' => $event->sender->id, 'object_model' => get_class($event->sender))) as $like) {
            $like->delete();
        }
    }

    /**
     * On run of integrity check command, validate all module data
     *
     * @param type $event
     */
    public static function onIntegrityCheck($event)
    {

        $integrityChecker = $event->sender;
        $integrityChecker->showTestHeadline("Validating Like Module (" . Like::model()->count() . " entries)");

        foreach (Like::model()->findAll() as $l) {
            if ($l->source === null) {
                $integrityChecker->showFix("Deleting like id " . $l->id . " without existing target!");
                if (!$integrityChecker->simulate)
                    $l->delete();
            }
        }
    }

    /**
     * On initalizing the wall entry controls also add the like link widget
     *
     * @param type $event
     */
    public static function onWallEntryLinksInit($event)
    {
        $event->sender->addWidget(widgets\LikeLink::className(), array('object' => $event->sender->object), array('sortOrder' => 10));
    }

}
