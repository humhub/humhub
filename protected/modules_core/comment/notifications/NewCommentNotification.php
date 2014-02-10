<?php

/**
 * Description of NotifyNewComment
 *
 * @package humhub.modules_core.comment.notifications
 * @since 0.5
 */
class NewCommentNotification extends Notification {

    public $webView = "comment.views.notifications.newComment";
    public $mailView = "application.modules_core.comment.views.notifications.newComment_mail";

    public static function fire($comment) {

        // Get Comment Root
        $createdByUserId = $comment->getContentObject()->created_by;

        if ($createdByUserId != $comment->created_by) {

            // Send Notification to owner
            $notification = new Notification();
            $notification->class = "NewCommentNotification";
            $notification->user_id = $createdByUserId;
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
