<?php

/**
 * NotifyAlsoCommented
 *
 * Output Example: X also commented Obj "Bla Bla Bla")
 * Fires at: Comment Create
 * Users: All other comment users
 * Deleted at: Delete Comment
 *
 * @package humhub.modules_core.comment.notifications
 * @since 0.5
 */
class AlsoCommentedNotification extends Notification {

    public $webView = "comment.views.notifications.alsoCommented";
    public $mailView = "application.modules_core.comment.views.notifications.alsoCommented_mail";

    /**
     * Fire this notification on given comment object
     *
     * @param type $comment
     */
    public static function fire($comment) {
        $targetCreatorId = $comment->content->user_id;     // gets also an new comment notification
        // Get Users which are also commented this model
        $userIds = array();
        $otherComments = Comment::model()->findAllByAttributes(array('object_model' => $comment->object_model, 'object_id' => $comment->object_id));
        foreach ($otherComments as $otherComment) {
            if ($comment->created_by != $otherComment->created_by && $otherComment->created_by != $targetCreatorId)
                $userIds[] = $otherComment->created_by;
        }
        $userIds = array_unique($userIds);

        // Write new Notification for them
        foreach ($userIds as $userId) {

            $notification = new Notification();
            $notification->class = "AlsoCommentedNotification";
            $notification->user_id = $userId;
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
