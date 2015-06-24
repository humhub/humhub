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
 * SpaceApprovalRequestAcceptedNotification
 *
 * @package humhub.modules_core.space.notifications
 * @since 0.5
 * @todo Move to space module
 */
class SpaceApprovalRequestAcceptedNotification extends Notification {

    public $webView = "space.views.notifications.approvalRequestAccepted";
    public $mailView = "application.modules_core.space.views.notifications.approvalRequestAccepted_mail";

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

    public function redirectToTarget() {

        $space = $this->getTargetObject();
        Yii::app()->getController()->redirect($space->getUrl());
    }

}

?>