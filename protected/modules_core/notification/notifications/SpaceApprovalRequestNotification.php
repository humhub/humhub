<?php

/**
 * SpaceApprovalRequestNotification
 *
 * @todo Move to space module
 * @package humhub.modules_core.notification.notifications
 * @since 0.5
 */
class SpaceApprovalRequestNotification extends Notification {

    public $webView = "notification.views.notifications.spaceApprovalRequest";
    public $mailView = "application.modules_core.notification.views.notifications.spaceApprovalRequest_mail";

    public static function fire($userId, $workspace) {

        // Get Approval Users
        $admins = $workspace->getAdmins();

        $user = User::model()->findByPk($userId);

        // Send them a notification
        foreach ($admins as $admin) {

            // Send Notification to owner
            $notification = new Notification();
            $notification->class = "SpaceApprovalRequestNotification";
            $notification->user_id = $admin->id;
            $notification->space_id = $workspace->id;

            $notification->source_object_model = "User";
            $notification->source_object_id = $user->id;

            $notification->target_object_model = "Space";
            $notification->target_object_id = $workspace->id;

            $notification->save();
        }
    }

    /**
     * Remove notification after member was approved/declined or canceled the
     * request.
     *
     * @param type $userId
     * @param type $workspace
     */
    public static function remove($userId, $workspace) {

        $notifications = Notification::model()->findAllByAttributes(array(
            'class' => 'SpaceApprovalRequestNotification',
            'target_object_model' => 'Space',
            'target_object_id' => $workspace->id,
            'source_object_model' => 'User',
            'source_object_id' => $userId
        ));

        foreach ($notifications as $notification) {
            $notification->delete();
        }
    }

    public function redirectToTarget() {

        $space = $this->getTargetObject();
        Yii::app()->getController()->redirect($space->getUrl());
    }

}

?>
