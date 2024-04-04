<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\modules\comment\models\Comment;
use Yii;
use yii\base\Component;
use yii\base\Event;

/**
 * Events provides callbacks to handle events.
 *
 * @author luke
 */
class Events extends Component
{
    /**
     * On content deletion make sure to delete all its comments
     *
     * @param Event $event
     */
    public static function onContentDelete($event)
    {
        /** @var Comment|ContentActiveRecord $sender */
        $sender = $event->sender;

        foreach (Comment::find()->where(['object_model' => get_class($sender), 'object_id' => $sender->getPrimaryKey()])->all() as $comment) {
            $comment->delete();
        }
    }

    /**
     * On User delete, also delete all comments
     *
     * @param Event $event
     */
    public static function onUserDelete($event)
    {
        foreach (Comment::findAll(['created_by' => $event->sender->id]) as $comment) {
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
        $integrityController->showTestHeadline('Comment Module (' . Comment::find()->count() . ' entries)');

        // Loop over all comments
        foreach (Comment::find()->each() as $c) {

            // Check underlying record exists
            if ($c->source === null) {
                if ($integrityController->showFix('Deleting comment id ' . $c->id . ' without existing target!')) {
                    $c->delete();
                }
            }

            // User exists
            if ($c->user === null) {
                if ($integrityController->showFix('Deleting comment id ' . $c->id . ' without existing user!')) {
                    $c->delete();
                }
            }
        }
    }

    /**
     * On init of the WallEntryLinksWidget, attach the comment link widget.
     *
     * @param Event $event
     */
    public static function onWallEntryLinksInit($event)
    {
        if ($event->sender->object->content === null) {
            return;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('comment');

        if ($module->canComment($event->sender->object)) {
            $event->sender->addWidget(widgets\CommentLink::class, ['object' => $event->sender->object], ['sortOrder' => 10]);
        }
    }

    /**
     * On init of the WallEntryAddonWidget, attach the comment widget.
     *
     * @param Event $event
     */
    public static function onWallEntryAddonInit($event)
    {
        /* @var WallEntryAddons $wallEntryAddons */
        $wallEntryAddons = $event->sender;

        $wallEntryAddons->addWidget(widgets\Comments::class, [
            'object' => $wallEntryAddons->object,
            'renderOptions' => $wallEntryAddons->renderOptions,
        ], ['sortOrder' => 30]);
    }

}
