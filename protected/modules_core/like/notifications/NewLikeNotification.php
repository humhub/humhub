<?php

/**
 * Notifies a user about likes of his objects (posts, comments, tasks & co)
 *
 * @package humhub.modules_core.like.notifications
 * @since 0.5
 */
class NewLikeNotification extends Notification
{

    public $webView = "like.views.notifications.newLike";
    public $mailView = "application.modules_core.like.views.notifications.newLike_mail";

    /**
     * Fires this notification
     *
     * @param type $like
     */
    public static function fire($like)
    {
        foreach ($like->content->getUnderlyingObject()->getFollowers(null, true) as $user) {

            if ($user->id == $like->created_by) {
                continue;
            }

            $notification = new Notification();
            $notification->class = "NewLikeNotification";
            $notification->user_id = $user->id;

            if ($like->content->container instanceof Space) {
                $notification->space_id = $like->content->space_id;
            }

            $notification->source_object_model = "Like";
            $notification->source_object_id = $like->id;

            $notification->target_object_model = $like->object_model;
            $notification->target_object_id = $like->object_id;

            $notification->save();
        }
    }

}

?>
