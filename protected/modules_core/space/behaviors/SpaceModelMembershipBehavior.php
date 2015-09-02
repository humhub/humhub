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
 * SpaceModelMemberBehavior bundles all membership related methods of
 * the Space model.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.6
 */
class SpaceModelMembershipBehavior extends CActiveRecordBehavior
{

    private $_spaceOwner = null;

    /**
     * Checks if given Userid is Member of this Space.
     *
     * @param type $userId
     * @return type
     */
    public function isMember($userId = "")
    {

        // Take current userid if none is given
        if ($userId == "")
            $userId = Yii::app()->user->id;

        $membership = $this->getMembership($userId);

        if ($membership != null && $membership->status == SpaceMembership::STATUS_MEMBER)
            return true;

        return false;
    }

    /**
     * Checks if given Userid is Admin of this Space.
     *
     * If no UserId is given, current UserId will be used
     *
     * @param type $userId
     * @return type
     */
    public function isAdmin($userId = "")
    {

        if ($userId == 0)
            $userId = Yii::app()->user->id;

        if (Yii::app()->user->isAdmin())
            return true;

        if ($this->isSpaceOwner($userId))
            return true;

        $membership = $this->getMembership($userId);

        if ($membership != null && $membership->admin_role == 1 && $membership->status == SpaceMembership::STATUS_MEMBER)
            return true;

        return false;
    }

    /**
     * Sets Owner for this workspace
     *
     * @param type $userId
     * @return type
     */
    public function setSpaceOwner($userId = "")
    {

        if ($userId == 0)
            $userId = Yii::app()->user->id;

        $this->setAdmin($userId);

        $this->getOwner()->created_by = $userId;
        $this->getOwner()->save();

        $this->_spaceOwner = null;

        return true;
    }

    /**
     * Gets Owner for this workspace
     *
     * @return type
     */
    public function getSpaceOwner()
    {

        if ($this->_spaceOwner != null) {
            return $this->_spaceOwner;
        }

        $this->_spaceOwner = User::model()->findByPk($this->getOwner()->created_by);
        return $this->_spaceOwner;
    }

    /**
     * Is given User owner of this Space
     */
    public function isSpaceOwner($userId = "")
    {
        if ($userId == "")
            $userId = Yii::app()->user->id;

        if ($this->getSpaceOwner()->id == $userId) {
            return true;
        }

        return false;
    }

    /**
     * Sets Owner for this workspace
     *
     * @param type $userId
     * @return type
     */
    public function setAdmin($userId = "")
    {

        if ($userId == 0)
            $userId = Yii::app()->user->id;

        $membership = $this->getMembership($userId);
        if ($membership != null) {
            $membership->admin_role = 1;
            $membership->save();
            return true;
        }
        return false;
    }

    /**
     * Returns the SpaceMembership Record for this Space
     *
     * If none Record is found, null is given
     */
    public function getMembership($userId = "")
    {
        if ($userId == "")
            $userId = Yii::app()->user->id;

        $rCacheId = 'SpaceMembership_' . $userId . "_" . $this->getOwner()->id;
        $rCacheRes = RuntimeCache::Get($rCacheId);

        if ($rCacheRes != null)
            return $rCacheRes;

        $dbResult = SpaceMembership::model()->findByAttributes(array('user_id' => $userId, 'space_id' => $this->getOwner()->id));
        RuntimeCache::Set($rCacheId, $dbResult);

        return $dbResult;
    }

    /**
     * Invites a not registered member to this space
     *
     * @param type $email
     * @param type $originatorUserId
     */
    public function inviteMemberByEMail($email, $originatorUserId)
    {

        // Invalid E-Mail
        $validator = new CEmailValidator;
        if (!$validator->validateValue($email))
            return false;

        // User already registered
        $user = User::model()->findByAttributes(array('email' => $email));
        if ($user != null)
            return false;

        $userInvite = UserInvite::model()->findByAttributes(array('email' => $email));

        // No invite yet
        if ($userInvite == null) {
            // Invite EXTERNAL user
            $userInvite = new UserInvite();
            $userInvite->email = $email;
            $userInvite->source = UserInvite::SOURCE_INVITE;
            $userInvite->user_originator_id = $originatorUserId;
            $userInvite->space_invite_id = $this->getOwner()->id;
            $userInvite->save();
            $userInvite->sendInviteMail();

            // There is a pending registration
            // Steal it und send mail again
            // Unfortunately there a no multiple workspace invites supported
            // so we take the last one
        } else {
            $userInvite->user_originator_id = $originatorUserId;
            $userInvite->space_invite_id = $this->getOwner()->id;
            $userInvite->save();
            $userInvite->sendInviteMail();
        }
        return true;
    }

    /**
     * Requests Membership
     *
     * @param type $userId
     * @param type $message
     */
    public function requestMembership($userId, $message = "")
    {

        // Add Membership
        $membership = new SpaceMembership;
        $membership->space_id = $this->getOwner()->id;
        $membership->user_id = $userId;
        $membership->status = SpaceMembership::STATUS_APPLICANT;
        $membership->invite_role = 0;
        $membership->admin_role = 0;
        $membership->share_role = 0;
        $membership->request_message = $message;
        $membership->save();

        SpaceApprovalRequestNotification::fire($userId, $this->getOwner());
    }

    /**
     * Returns the Admins of this Space
     */
    public function getAdmins()
    {

        $admins = array();

        $adminMemberships = SpaceMembership::model()->findAllByAttributes(array('space_id' => $this->getOwner()->id, 'admin_role' => 1));

        foreach ($adminMemberships as $admin) {
            $admins[] = $admin->user;
        }

        return $admins;
    }

    /**
     * Invites a registered user to this space
     *
     * If user is already invited, retrigger invitation.
     * If user is applicant approve it.
     *
     * @param type $userId
     * @param type $originatorUserId
     */
    public function inviteMember($userId, $originatorUserId)
    {

        $membership = $this->getMembership($userId);

        if ($membership != null) {

            // User is already member
            if ($membership->status == SpaceMembership::STATUS_MEMBER) {
                return;
            }

            // User requested already membership, just approve him
            if ($membership->status == SpaceMembership::STATUS_APPLICANT) {
                $space->addMember(Yii::app()->user->id);
                return;
            }

            // Already invite, reinvite him
            if ($membership->status == SpaceMembership::STATUS_INVITED) {
                // Remove existing notification
                SpaceInviteNotification::remove($userId, $this->getOwner());
            }
        } else {
            $membership = new SpaceMembership;
        }


        $membership->space_id = $this->getOwner()->id;
        $membership->user_id = $userId;
        $membership->originator_user_id = $originatorUserId;

        $membership->status = SpaceMembership::STATUS_INVITED;
        $membership->invite_role = 0;
        $membership->admin_role = 0;
        $membership->share_role = 0;

        $membership->save();

        SpaceInviteNotification::fire($originatorUserId, $userId, $this->getOwner());
    }

    /**
     * Adds an member to this space.
     *
     * This can happens after an clicking "Request Membership" Link
     * after Approval or accepting an invite.
     *
     * @param type $userId
     */
    public function addMember($userId)
    {

        $user = User::model()->findByPk($userId);
        $membership = $this->getMembership($userId);

        if ($membership == null) {
            // Add Membership
            $membership = new SpaceMembership;
            $membership->space_id = $this->getOwner()->id;
            $membership->user_id = $userId;
            $membership->status = SpaceMembership::STATUS_MEMBER;
            $membership->invite_role = 0;
            $membership->admin_role = 0;
            $membership->share_role = 0;
            
            $userInvite = UserInvite::model()->findByAttributes(array('email' => $user->email));
            if ($userInvite !== null && $userInvite->source == UserInvite::SOURCE_INVITE) {
                SpaceInviteAcceptedNotification::fire($userInvite->user_originator_id, $user, $this->getOwner());
            }
        } else {

            // User is already member
            if ($membership->status == SpaceMembership::STATUS_MEMBER) {
                return true;
            }

            // User requested membership
            if ($membership->status == SpaceMembership::STATUS_APPLICANT) {
                SpaceApprovalRequestAcceptedNotification::fire(Yii::app()->user->id, $user, $this->getOwner());
            }

            // User was invited
            if ($membership->status == SpaceMembership::STATUS_INVITED) {
                SpaceInviteAcceptedNotification::fire($membership->originator_user_id, $user, $this->getOwner());
            }

            // Update Membership
            $membership->status = SpaceMembership::STATUS_MEMBER;
        }
        $membership->save();

        // Create Wall Activity for that
        $activity = new Activity;
        $activity->content->space_id = $this->getOwner()->id;
        $activity->content->visibility = Content::VISIBILITY_PRIVATE;
        $activity->content->created_by = $this->getOwner()->id;
        $activity->created_by = $userId;
        $activity->type = "ActivitySpaceMemberAdded";
        $activity->save();
        $activity->fire();

        // Members can't also follow the space
        $this->getOwner()->unfollow($userId);

        // Cleanup Notifications
        SpaceInviteNotification::remove($userId, $this->getOwner());
        SpaceApprovalRequestNotification::remove($userId, $this->getOwner());
    }

    /**
     * Remove Membership
     *
     * @param $userId UserId of User to Remove
     */
    public function removeMember($userId = "")
    {

        if ($userId == "")
            $userId = Yii::app()->user->id;

        $user = User::model()->findByPk($userId);
        $membership = $this->getMembership($userId);


        if ($this->isSpaceOwner($userId)) {
            return false;
        }

        if ($membership == null) {
            return true;
        }

        // If was member, create a activity for that
        if ($membership->status == SpaceMembership::STATUS_MEMBER) {
            $activity = new Activity;
            $activity->content->space_id = $this->getOwner()->id;
            $activity->content->visibility = Content::VISIBILITY_PRIVATE;
            $activity->type = "ActivitySpaceMemberRemoved";
            $activity->created_by = $userId;
            $activity->save();
            $activity->fire();
        }

        // Was invited, but declined the request
        if ($membership->status == SpaceMembership::STATUS_INVITED) {
            SpaceInviteDeclinedNotification::fire($membership->originator_user_id, $user, $this->getOwner());
        }

        foreach (SpaceMembership::model()->findAllByAttributes(array(
            'user_id' => $userId,
            'space_id' => $this->getOwner()->id,
        )) as $membership) {
            $membership->delete();
        }

        // Cleanup Notifications
        SpaceApprovalRequestNotification::remove($userId, $this->getOwner());
        SpaceInviteNotification::remove($userId, $this->getOwner());
        SpaceApprovalRequestNotification::remove($userId, $this->getOwner());
    }

}
