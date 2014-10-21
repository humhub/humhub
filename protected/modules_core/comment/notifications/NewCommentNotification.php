<?php

/**
 * Description of NotifyNewComment
 *
 * @package humhub.modules_core.comment.notifications
 * @since 0.5
 */
class NewCommentNotification extends Notification
{

    public $webView = "comment.views.notifications.newComment";
    public $mailView = "application.modules_core.comment.views.notifications.newComment_mail";

    /**
     * Sends an notifcation to everybody who is involved/following in this content
     * with notifications.
     * 
     * @param HActiveRecordContentAddon $comment
     */
    public static function fire($comment)
    {

        foreach ($comment->content->getUnderlyingObject()->getFollowers(null, true) as $user) {

            // Dont send a notification to the creator of this comment
            if ($user->id == $comment->created_by) {
                continue;
            }

            // Check there is also an mentioned notifications, so ignore this notification
            if (Notification::model()->findByAttributes(array('class' => 'MentionedNotification', 'source_object_model' => 'Comment', 'source_object_id' => $comment->id)) !== null) {
                continue;
            }

            $notification = new Notification();
            $notification->class = "NewCommentNotification";
            $notification->user_id = $user->id;

            // Optional
            $notification->space_id = $comment->space_id;

            $notification->source_object_model = "Comment";
            $notification->source_object_id = $comment->id;

            $notification->target_object_model = $comment->object_model;
            $notification->target_object_id = $comment->object_id;

            $notification->save();
        }
    }

}

?>
