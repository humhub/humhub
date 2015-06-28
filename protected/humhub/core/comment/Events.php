<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\core\comment;

/**
 * Description of Events
 *
 * @author luke
 */
class Events extends \yii\base\Object
{

    /**
     * On content deletion make sure to delete all its comments
     *
     * @param CEvent $event
     */
    public static function onContentDelete($event)
    {

        foreach (models\Comment::find()->where(['object_model' => $event->sender->className(), 'object_id' => $event->sender->id])->all() as $comment) {
            $comment->delete();
        }
    }

    /**
     * On User delete, also delete all comments
     *
     * @param CEvent $event
     */
    public static function onUserDelete($event)
    {

        foreach (Comment::model()->findAllByAttributes(array('created_by' => $event->sender->id)) as $comment) {
            $comment->delete();
        }
        return true;
    }

    /**
     * On run of integrity check command, validate all module data
     *
     * @param CEvent $event
     */
    public static function onIntegrityCheck($event)
    {

        $integrityChecker = $event->sender;
        $integrityChecker->showTestHeadline("Validating Comment Module (" . Comment::model()->count() . " entries)");

        // Loop over all comments
        foreach (Comment::model()->findAll() as $c) {

            if ($c->source === null) {
                $integrityChecker->showFix("Deleting comment id " . $c->id . " without existing target!");
                if (!$integrityChecker->simulate)
                    $c->delete();
            }
        }
    }

    /**
     * On init of the WallEntryLinksWidget, attach the comment link widget.
     *
     * @param CEvent $event
     */
    public static function onWallEntryLinksInit($event)
    {
        $event->sender->addWidget(widgets\Link::className(), array('object' => $event->sender->object), array('sortOrder' => 10));
    }

    /**
     * On init of the WallEntryAddonWidget, attach the comment widget.
     *
     * @param CEvent $event
     */
    public static function onWallEntryAddonInit($event)
    {
        $event->sender->addWidget(widgets\Comments::className(), array('object' => $event->sender->object), array('sortOrder' => 20));
    }

}
