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
 * SpaceInviteDeclinedNotification is sent to the originator of the invite to 
 * inform him about the decline.
 *
 * @package humhub.modules.space.notifications
 * @since 0.5
 * @author Luke
 */
class SpaceInviteDeclinedNotification extends Notification {

    // Path to Web View of this Notification
    public $webView = "space.views.notifications.inviteDeclined";
    // Path to Mail Template for this notification
    public $mailView = "application.modules_core.space.views.notifications.inviteDeclined_mail";

    public static function fire($invitorUserId, $invitedUser, $space) {

        // Send Notification to owner
        $notification = new Notification();
        $notification->class = "SpaceInviteDeclinedNotification";
        $notification->user_id = $invitorUserId;
        $notification->space_id = $space->id;

        $notification->source_object_model = "User";
        $notification->source_object_id = $invitedUser->id;

        $notification->target_object_model = "Space";
        $notification->target_object_id = $space->id;

        $notification->save();
    }

    public function redirectToTarget() {
        Yii::app()->getController()->redirect($this->space->getUrl());
    }

}

?>
