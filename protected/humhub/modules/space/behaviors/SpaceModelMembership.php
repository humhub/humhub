<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\behaviors;

use Yii;
use yii\base\Behavior;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\space\models\Membership;
use humhub\modules\user\models\Invite;

/**
 * SpaceModelMemberBehavior bundles all membership related methods of the Space model.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 */
class SpaceModelMembership extends Behavior
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
            $userId = Yii::$app->user->id;

        $membership = $this->getMembership($userId);

        if ($membership != null && $membership->status == Membership::STATUS_MEMBER)
            return true;

        return false;
    }
    
    /**
     * Checks if a given Userid is allowed to leave this space.
     * A User is allowed to leave, if the can_cancel_membership flag in the space_membership table is 1. If it is 2, the decision is delegated to the space.
     * 
     * @param number $userId, if empty hte currently logged in user is taken.
     * @return bool
     */
    public function canLeave($userId = "")
    {
    
        // Take current userid if none is given
        if ($userId == "")
            $userId = Yii::$app->user->id;
    
        $membership = $this->getMembership($userId);

        if ($membership != null && !empty($membership->can_cancel_membership)) {
            return $membership->can_cancel_membership === 1 || ($membership->can_cancel_membership === 2 && !empty($this->owner->members_can_leave));
        }          
    
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
            $userId = Yii::$app->user->id;

        if (Yii::$app->user->isAdmin())
            return true;

        if ($this->isSpaceOwner($userId))
            return true;

        $membership = $this->getMembership($userId);

        if ($membership !== null && $membership->group_id == Space::USERGROUP_ADMIN && $membership->status == Membership::STATUS_MEMBER)
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
            $userId = Yii::$app->user->id;

        $this->setAdmin($userId);

        $this->owner->created_by = $userId;
        $this->owner->update(false, ['created_by']);

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

        $this->_spaceOwner = User::findOne(['id' => $this->owner->created_by]);
        return $this->_spaceOwner;
    }

    /**
     * Is given User owner of this Space
     */
    public function isSpaceOwner($userId = "")
    {
        if ($userId == "")
            $userId = Yii::$app->user->id;

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
            $userId = Yii::$app->user->id;

        $membership = $this->getMembership($userId);
        if ($membership != null) {
            $membership->group_id = Space::USERGROUP_ADMIN;
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
            $userId = Yii::$app->user->id;

        return Membership::findOne(['user_id' => $userId, 'space_id' => $this->owner->id]);
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
        $validator = new \yii\validators\EmailValidator;
        if (!$validator->validate($email))
            return false;

        // User already registered
        $user = User::findOne(['email' => $email]);
        if ($user != null)
            return false;

        $userInvite = Invite::findOne(['email' => $email]);
        // No invite yet
         if ($userInvite == null) {
            // Invite EXTERNAL user
            $userInvite = new Invite();
            $userInvite->email = $email;
            $userInvite->source = Invite::SOURCE_INVITE;
            $userInvite->user_originator_id = $originatorUserId;
            $userInvite->space_invite_id = $this->owner->id;
            // There is a pending registration
            // Steal it und send mail again
            // Unfortunately there a no multiple workspace invites supported
            // so we take the last one
        } else {
            $userInvite->user_originator_id = $originatorUserId;
            $userInvite->space_invite_id = $this->owner->id;
        }
        
        if($userInvite->validate() && $userInvite->save()) {
            $userInvite->sendInviteMail();
            return true;
        } 
        
        return false;
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
        $membership = new Membership;
        $membership->space_id = $this->owner->id;
        $membership->user_id = $userId;
        $membership->status = Membership::STATUS_APPLICANT;
        $membership->group_id = Space::USERGROUP_MEMBER;
        $membership->request_message = $message;
        $membership->save();

        $notification = new \humhub\modules\space\notifications\ApprovalRequest();
        $notification->source = $this->owner;
        $notification->originator = User::findOne(['id' => $userId]);
        $notification->sendBulk($this->getAdmins());
    }

    /**
     * Returns the Admins of this Space
     */
    public function getAdmins()
    {
        $admins = array();
        $adminMemberships = Membership::findAll(['space_id' => $this->owner->id, 'group_id' => Space::USERGROUP_ADMIN]);

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
            if ($membership->status == Membership::STATUS_MEMBER) {
                return;
            }

            // User requested already membership, just approve him
            if ($membership->status == Membership::STATUS_APPLICANT) {
                $this->addMember(Yii::$app->user->id);
                return;
            }

            // Already invite, reinvite him
            if ($membership->status == Membership::STATUS_INVITED) {
                // Remove existing notification
                $notification = new \humhub\modules\space\notifications\Invite;
                $notification->source = $this->owner;
                $notification->delete(User::findOne(['id' => $userId]));
            }
        } else {
            $membership = new Membership;
        }


        $membership->space_id = $this->owner->id;
        $membership->user_id = $userId;
        $membership->originator_user_id = $originatorUserId;

        $membership->status = Membership::STATUS_INVITED;
        $membership->group_id = Space::USERGROUP_MEMBER;

        if (!$membership->save()) {
            throw new \yii\base\Exception("Could not save membership!" . print_r($membership->getErrors(), 1));
        }

        $notification = new \humhub\modules\space\notifications\Invite;
        $notification->source = $this->owner;
        $notification->originator = User::findOne(['id' => $originatorUserId]);
        $notification->send(User::findOne(['id' => $userId]));
    }

    /**
     * Adds an member to this space.
     *
     * This can happens after an clicking "Request Membership" Link
     * after Approval or accepting an invite.
     *
     * @param type $userId
     * @param type $canLeave 0: user cannot cancel membership | 1: can cancel membership | 2: depending on space flag members_can_leave
     */
    public function addMember($userId, $canLeave = 1)
    {
        $user = User::findOne(['id' => $userId]);
        $membership = $this->getMembership($userId);

        if ($membership == null) {
            // Add Membership
            $membership = new Membership;
            $membership->space_id = $this->owner->id;
            $membership->user_id = $userId;
            $membership->status = Membership::STATUS_MEMBER;
            $membership->group_id = Space::USERGROUP_MEMBER;
            $membership->can_cancel_membership = $canLeave;

            $userInvite = Invite::findOne(['email' => $user->email]);
            if ($userInvite !== null && $userInvite->source == Invite::SOURCE_INVITE) {
                $notification = new \humhub\modules\space\notifications\InviteAccepted();
                $notification->originator = $user;
                $notification->source = $this->owner;
                $notification->send(User::findOne(['id' => $userInvite->user_originator_id]));
            }
        } else {

            // User is already member
            if ($membership->status == Membership::STATUS_MEMBER) {
                return true;
            }

            // User requested membership
            if ($membership->status == Membership::STATUS_APPLICANT) {
                $notification = new \humhub\modules\space\notifications\ApprovalRequestAccepted();
                $notification->source = $this->owner;
                $notification->originator = Yii::$app->user->getIdentity();
                $notification->send($user);
            }

            // User was invited
            if ($membership->status == Membership::STATUS_INVITED) {
                $notification = new \humhub\modules\space\notifications\InviteAccepted();
                $notification->source = $this->owner;
                $notification->originator = $user;
                $notification->send(User::findOne(['id' => $membership->originator_user_id]));
            }

            // Update Membership
            $membership->status = Membership::STATUS_MEMBER;
        }
        $membership->save();

        $activity = new \humhub\modules\space\activities\MemberAdded;
        $activity->source = $this->owner;
        $activity->originator = $user;
        $activity->create();

        // Members can't also follow the space
        $this->owner->unfollow($userId);

        // Delete invite notification for this user
        $notificationInvite = new \humhub\modules\space\notifications\Invite;
        $notificationInvite->source = $this->owner;
        $notificationInvite->delete($user);

        // Delete pending approval request notifications for this user
        $notificationApprovalRequest = new \humhub\modules\space\notifications\ApprovalRequest();
        $notificationApprovalRequest->source = $this->owner;
        $notificationApprovalRequest->originator = $user;
        $notificationApprovalRequest->delete();
    }

    /**
     * Remove Membership
     *
     * @param $userId UserId of User to Remove
     */
    public function removeMember($userId = "")
    {
        if ($userId == "")
            $userId = Yii::$app->user->id;

        $user = User::findOne(['id' => $userId]);

        $membership = $this->getMembership($userId);

        if ($this->isSpaceOwner($userId)) {
            return false;
        }

        if ($membership == null) {
            return true;
        }

        // If was member, create a activity for that
        if ($membership->status == Membership::STATUS_MEMBER) {
            $activity = new \humhub\modules\space\activities\MemberRemoved();
            $activity->source = $this->owner;
            $activity->originator = $user;
            $activity->create();
        } elseif ($membership->status == Membership::STATUS_INVITED && $membership->originator !== null) {
            // Was invited, but declined the request - inform originator
            $notification = new \humhub\modules\space\notifications\InviteDeclined();
            $notification->source = $this->owner;
            $notification->originator = $user;
            $notification->send($membership->originator);
        } elseif ($membership->status == Membership::STATUS_APPLICANT) {
            $notification = new \humhub\modules\space\notifications\ApprovalRequestDeclined();
            $notification->source = $this->owner;
            $notification->originator = Yii::$app->user->getIdentity();
            $notification->send($user);
        }

        foreach (Membership::findAll(['user_id' => $userId, 'space_id' => $this->owner->id]) as $membership) {
            $membership->delete();
        }

        $notificationApproval = new \humhub\modules\space\notifications\ApprovalRequest();
        $notificationApproval->source = $this->owner;
        $notificationApproval->originator = $user;
        $notificationApproval->delete();

        $notificationInvite = new \humhub\modules\space\notifications\Invite();
        $notificationInvite->source = $this->owner;
        $notificationInvite->delete($user);
    }

}
