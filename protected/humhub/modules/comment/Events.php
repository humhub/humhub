<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment;

use humhub\modules\comment\models\Comment;

/**
 * Events provides callbacks to handle events.
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

        foreach (Comment::findAll(array('created_by' => $event->sender->id)) as $comment) {
            $comment->delete();
        }
        return true;
    }

    /**
     * Callback to validate module database records.
     *
     * @param Event $event
     */
    public static function onIntegrityCheck($event)
    {
        $integrityController = $event->sender;
        $integrityController->showTestHeadline("Comment Module (" . Comment::find()->count() . " entries)");

        // Loop over all comments
        foreach (Comment::find()->all() as $c) {

            // Check underlying record exists
            if ($c->source === null) {
                if ($integrityController->showFix("Deleting comment id " . $c->id . " without existing target!")) {
                    $c->delete();
                }
            }

            // User exists
            if ($c->user === null) {
                if ($integrityController->showFix("Deleting comment id " . $c->id . " without existing user!")) {
                    $c->delete();
                }
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
        if ($event->sender->object->content === null) {
            return;
        }
        
        if (\Yii::$app->getModule('comment')->canComment($event->sender->object->content)) {
            $event->sender->addWidget(widgets\CommentLink::className(), array('object' => $event->sender->object), array('sortOrder' => 10));
        }
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
