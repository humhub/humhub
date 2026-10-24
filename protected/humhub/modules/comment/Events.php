<?php

namespace humhub\modules\comment;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\events\ContentEvent;
use humhub\modules\content\widgets\WallEntryAddons;
use humhub\modules\comment\models\Comment;
use Yii;
use yii\base\Component;
use yii\base\Event;

class Events extends Component
{
    public static function onContentDelete(Event $event)
    {
        /** @var ContentActiveRecord $sender */
        $sender = $event->sender;

        foreach (Comment::find()->where(['content_id' => $sender->content->id])->all() as $comment) {
            $comment->delete();
        }
    }

    /**
     * On hard delete of a Content record without a polymorphic content object
     * (e.g. cleaned up by the IntegrityController), delete all its comments too.
     */
    public static function onContentHardDelete(ContentEvent $event)
    {
        foreach (Comment::findAll(['content_id' => $event->content->id]) as $comment) {
            $comment->delete();
        }
    }

    public static function onUserDelete(Event $event)
    {
        foreach (Comment::findAll(['created_by' => $event->sender->id]) as $comment) {
            $comment->delete();
        }

        return true;
    }

    public static function onIntegrityCheck(Event $event)
    {
        $integrityController = $event->sender;
        $integrityController->showTestHeadline('Comment Module (' . Comment::find()->count() . ' entries)');

        // Loop over all comments
        /** @var Comment $c */
        foreach (Comment::find()->each() as $c) {

            // Check underlying record exists
            if (
                !$c->getContent()->exists()
                && $integrityController->showFix('Deleting comment id ' . $c->id . ' without existing target!')
            ) {
                $c->delete();
                continue;
            }

            // User exists
            if (
                !$c->getCreatedBy()->exists()
                && $integrityController->showFix('Deleting comment id ' . $c->id . ' without existing user!')
            ) {
                $c->delete();
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

        $content = $event->sender->object->content;

        /** @var Module $module */
        $module = Yii::$app->getModule('comment');

        if ((Yii::$app->user->isGuest && $module->guestHideComments) || $module->canComment($content)) {
            $event->sender->addWidget(widgets\CommentLink::class, ['content' => $content], ['sortOrder' => 10]);
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
            'content' => $wallEntryAddons->object->content,
            'renderOptions' => $wallEntryAddons->renderOptions,
        ], ['sortOrder' => 30]);
    }

}
