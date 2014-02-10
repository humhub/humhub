<?php

/**
 * NotifyAlsoLikes
 *
 * When another user also likes something, the user will be notified.
 *
 * @package humhub.modules_core.like.notifications
 * @since 0.5
 */
class AlsoLikesNotification extends Notification {

    public $webView = "like.views.notifications.alsoLikes";
    public $mailView = "application.modules_core.like.views.notifications.alsoLikes_mail";

    /**
     * Fire this notification on given like object
     *
     * @param type $comment
     */
    public static function fire($like) {

        // Get Users which are also likes this model
        $userIds = array();

        $underlyingObject = $like->getUnderlyingObject();

        $otherLikes = Like::model()->findAllByAttributes(array('object_model' => $like->object_model, 'object_id' => $like->object_id));
        foreach ($otherLikes as $otherLike) {

            if ($like->created_by == $otherLike->created_by)
                continue;

            // This user will also gets a "New Like" notification
            if ($underlyingObject->created_by == $otherLike->created_by)
                continue;


            $userIds[] = $otherLike->created_by;
        }

        $userIds = array_unique($userIds);

        // Determine Space Id if exists
        $workspaceId = "";
        $contentBase = $like->getContentObject()->contentMeta->getContentBase();
        if (get_class($contentBase) == 'Space') {
            $workspaceId = $contentBase->id;
        }

        // Write new Notification for them
        foreach ($userIds as $userId) {

            $notification = new Notification();
            $notification->class = "AlsoLikesNotification";
            $notification->user_id = $userId;
            $notification->space_id = $workspaceId;

            // Which object throws this notification?
            $notification->source_object_model = 'Like';
            $notification->source_object_id = $like->id;

            // Which object gets this notifiction
            $notification->target_object_model = $like->object_model;
            $notification->target_object_id = $like->object_id;

            $notification->save();
        }
    }

}

?>
