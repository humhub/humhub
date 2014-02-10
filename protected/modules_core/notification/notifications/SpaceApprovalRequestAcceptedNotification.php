<?php

/**
 * SpaceApprovalRequestAcceptedNotification
 *
 * @package humhub.modules_core.notification.notifications
 * @since 0.5
 * @todo Move to space module
 */
class SpaceApprovalRequestAcceptedNotification extends Notification {

    public $webView = "notification.views.notifications.spaceApprovalRequestAccepted";
    public $mailView = "application.modules_core.notification.views.notifications.spaceApprovalRequestAccepted_mail";

    public static function fire($approverUserId, $requestor, $workspace) {

        // Send Notification to owner
        $notification = new Notification();
        $notification->class = "SpaceApprovalRequestAcceptedNotification";
        $notification->user_id = $requestor->id;
        $notification->space_id = $workspace->id;

        $notification->source_object_model = "User";
        $notification->source_object_id = $approverUserId;

        $notification->target_object_model = "Space";
        $notification->target_object_id = $workspace->id;

        $notification->save();
    }

    /**
     * Remove notification after member was approved/declined or canceled the
     * request.
     *
     * @param type $userId
     * @param type $workspace
     */
    public static function remove() {

    }

    public function redirectToTarget() {

        $space = $this->getTargetObject();
        Yii::app()->getController()->redirect($space->getUrl());
    }

}

?>
