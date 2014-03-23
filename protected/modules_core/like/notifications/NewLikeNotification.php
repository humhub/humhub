<?php

/**
 * Notifies a user about likes of his objects (posts, comments, tasks & co)
 *
 * @package humhub.modules_core.like.notifications
 * @since 0.5
 */
class NewLikeNotification extends Notification {

    public $webView = "like.views.notifications.newLike";
    public $mailView = "application.modules_core.like.views.notifications.newLike_mail";

    /**
     * Fires this notification
     *
     * @param type $like
     */
    public static function fire($like) {

        // Determine Space Id if exists
        $spaceId = "";
        if ($like->content->container instanceof Space) {
            $spaceId = $like->content->container->id;
        }

        if ($like->source->created_by != $like->created_by) {

            // Send Notification to owner
            $notification = new Notification();

            $notification->class = "NewLikeNotification";
            $notification->user_id = $like->source->created_by;
            $notification->space_id = $spaceId;

            $notification->source_object_model = "Like";
            $notification->source_object_id = $like->id;

            $notification->target_object_model = $like->object_model;
            $notification->target_object_id = $like->object_id;

            $notification->save();
        }
        
    }

}

?>
