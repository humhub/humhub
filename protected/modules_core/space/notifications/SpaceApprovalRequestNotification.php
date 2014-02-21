<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * SpaceApprovalRequestNotification
 *
 * @todo Move to space module
 * @package humhub.modules_core.space.notifications
 * @since 0.5
 */
class SpaceApprovalRequestNotification extends Notification {

    public $webView = "space.views.notifications.approvalRequest";
    public $mailView = "application.modules_core.space.views.notifications.approvalRequest_mail";

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
