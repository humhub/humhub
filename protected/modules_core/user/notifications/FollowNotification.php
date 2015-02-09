<?php

/**
 * FollowNotification is fired to all users that are being 
 * followed by other user
 */
class FollowNotification extends Notification
{

    // Path to Web View of this Notification
    public $webView = "user.views.notifications.follow";
    // Path to Mail Template for this notification
    public $mailView = "application.modules_core.user.views.notifications.follow_mail";

    
    public static function fire($user_follow){
        $notification = new Notification();
        $notification->class = "FollowNotification";
        $notification->user_id = $user_follow->object_id;
        
        $notification->source_object_model = "UserFollow";
        $notification->source_object_id = $user_follow->id;
        
        $notification->target_object_model = $user_follow->object_model;
        $notification->target_object_id = $user_follow->user_id;
        
        $notification->save();
    }

    public function redirectToTarget()
    {
        $user = $this->getTargetObject();
        Yii::app()->getController()->redirect($user->getUrl());
    }
}

?>
