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
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\space\notifications\ApprovalRequestAccepted;
use humhub\modules\space\notifications\InviteAccepted;
use humhub\modules\space\MemberEvent;

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
        if ($userId == "" && !Yii::$app->user->isGuest) {
            $userId = Yii::$app->user->id;
        } elseif ($userId == "" && Yii::$app->user->isGuest) {
            return false;
        }

        $membership = $this->getMembership($userId);

        if ($membership != null && $membership->status == Membership::STATUS_MEMBER) {
            return true;
        }

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
        if ($userId == "") {
            $userId = Yii::$app->user->id;
        }

        $membership = $this->getMembership($userId);

        if ($membership != null && !empty($membership->can_cancel_membership)) {
            return $membership->can_cancel_membership === 1 || ($membership->can_cancel_membership === 2 && !empty($this->owner->members_can_leave));
        }

        return false;
    }

    /**
     * Checks if given Userid is Admin of this Space or has the permission to manage spaces.
     *
     * If no UserId is given, current UserId will be used
     *
     * @param User|integer|null $user User instance or userId
     * @return boolean
     */
    public function isAdmin($user = null)
    {
        $userId = ($user instanceof User) ? $user->id : $user;

        if (empty($userId) && Yii::$app->user->can(new ManageSpaces())) {
            return true;
        }

        if (!$userId) {
            $userId = Yii::$app->user->id;
        }

        if ($this->isSpaceOwner($userId)) {
            return true;
        }

        $membership = $this->getMembership($userId);

        return ($membership && $membership->group_id == Space::USERGROUP_ADMIN && $membership->status == Membership::STATUS_MEMBER);
    }

    /**
     * Sets Owner for this workspace
     *
     * @param User|integer|null $userId
     * @return boolean
     */
    public function setSpaceOwner($user = null)
    {
        $userId = ($user instanceof User) ? $user->id : $user;

        if ($userId instanceof User) {
            $userId = $userId->id;
        } else if (!$userId || $userId == 0) {
            $userId = Yii::$app->user->id;
        }

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
     * @param User|int|null $userId
     * @return bool
     */
    public function isSpaceOwner($userId = null)
    {
        if(empty($userId) && Yii::$app->user->isGuest) {
            return false;
        } else if ($userId instanceof User) {
            $userId = $userId->id;
        }  else if (empty($userId)) {
            $userId = Yii::$app->user->id;
        }

        return $this->owner->created_by == $userId;
    }

    /**
     * Sets Owner for this workspace
     *
     * @param type $userId
     * @return type
     */
    public function setAdmin($userId = null)
    {
        if ($userId instanceof User) {
            $userId = $userId->id;
        } else if (!$userId || $userId == 0) {
            $userId = Yii::$app->user->id;
        }

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
    public function getMembership($userId = null)
    {
        if ($userId instanceof User) {
            $userId = $userId->id;
        } else if (!$userId || $userId == "") {
            $userId = Yii::$app->user->id;
        }

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

        if ($userInvite->validate() && $userInvite->save()) {
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

        $user = ($userId instanceof User) ? $userId : User::findOne(['id' => $userId]);

        // Add Membership
        $membership = new Membership([
            'space_id' => $this->owner->id,
            'user_id' => $user->id,
            'status' => Membership::STATUS_APPLICANT,
            'group_id' => Space::USERGROUP_MEMBER,
            'request_message' => $message
        ]);

        $membership->save();

        \humhub\modules\space\notifications\ApprovalRequest::instance()
                ->from($user)->about($this->owner)->withMessage($message)->sendBulk($this->getAdmins());
    }

    /**
     * Returns the Admins of this Space
     */
    public function getAdmins()
    {
        $admins = [];
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
     * @param type $originatorId
     */
    public function inviteMember($userId, $originatorId)
    {
        $membership = $this->getMembership($userId);

        if ($membership != null) {
            switch ($membership->status) {
                case Membership::STATUS_APPLICANT:
                    // If user is an applicant of this space add user and return.
                    $this->addMember(Yii::$app->user->id);
                case Membership::STATUS_MEMBER:
                    // If user is already a member just ignore the invitation. 
                    return;
                case Membership::STATUS_INVITED:
                    // If user is already invited, remove old invite notification and retrigger
                    $oldNotification = new \humhub\modules\space\notifications\Invite(['source' => $this->owner]);
                    $oldNotification->delete(User::findOne(['id' => $userId]));
                    break;
            }
        } else {
            $membership = new Membership([
                'space_id' => $this->owner->id,
                'user_id' => $userId,
                'status' => Membership::STATUS_INVITED,
                'group_id' => Space::USERGROUP_MEMBER
            ]);
        }

        // Update or set originator 
        $membership->originator_user_id = $originatorId;

        if ($membership->save()) {
            $this->sendInviteNotification($userId, $originatorId);
        } else {
            throw new \yii\base\Exception("Could not save membership!" . print_r($membership->getErrors(), 1));
        }
    }

    /**
     * Sends an Invite Notification to the given user.
     * 
     * @param type $userId
     * @param type $originatorId
     */
    protected function sendInviteNotification($userId, $originatorId)
    {
        $notification = new \humhub\modules\space\notifications\Invite([
            'source' => $this->owner,
            'originator' => User::findOne(['id' => $originatorId])
        ]);

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
            $membership = new Membership([
                'space_id' => $this->owner->id,
                'user_id' => $userId,
                'status' => Membership::STATUS_MEMBER,
                'group_id' => Space::USERGROUP_MEMBER,
                'can_cancel_membership' => $canLeave
            ]);

            $userInvite = Invite::findOne(['email' => $user->email]);

            if ($userInvite !== null && $userInvite->source == Invite::SOURCE_INVITE) {
                InviteAccepted::instance()->from($user)->about($this->owner)
                        ->send(User::findOne(['id' => $userInvite->user_originator_id]));
            }
        } else {

            // User is already member
            if ($membership->status == Membership::STATUS_MEMBER) {
                return true;
            }

            // User requested membership
            if ($membership->status == Membership::STATUS_APPLICANT) {
                ApprovalRequestAccepted::instance()
                        ->from(Yii::$app->user->getIdentity())->about($this->owner)->send($user);
            }

            // User was invited
            if ($membership->status == Membership::STATUS_INVITED) {
                InviteAccepted::instance()->from($user)->about($this->owner)
                        ->send(User::findOne(['id' => $membership->originator_user_id]));
            }

            // Update Membership
            $membership->status = Membership::STATUS_MEMBER;
        }

        $membership->save();

        MemberEvent::trigger(Membership::class, Membership::EVENT_MEMBER_ADDED, new MemberEvent([
            'space' => $this->owner, 'user' => $user
        ]));
        
        
        // Create Activity
        \humhub\modules\space\activities\MemberAdded::instance()->from($user)->about($this->owner)->save();

        // Members can't also follow the space
        $this->owner->unfollow($userId);

        // Delete invite notification for this user
        \humhub\modules\space\notifications\Invite::instance()->about($this->owner)->delete($user);

        // Delete pending approval request notifications for this user
        \humhub\modules\space\notifications\ApprovalRequest::instance()->from($user)->about($this->owner)->delete();
    }

    /**
     * Remove Membership
     *
     * @param $userId UserId of User to Remove
     */
    public function removeMember($userId = "")
    {
        if ($userId == "") {
            $userId = Yii::$app->user->id;
        }

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

            MemberEvent::trigger(Membership::class, Membership::EVENT_MEMBER_REMOVED, new MemberEvent([
                'space' => $this->owner, 'user' => $user
            ]));
        } elseif ($membership->status == Membership::STATUS_INVITED && $membership->originator !== null) {
            // Was invited, but declined the request - inform originator
            \humhub\modules\space\notifications\InviteDeclined::instance()
                ->from($user)->about($this->owner)->send($membership->originator);
        } elseif ($membership->status == Membership::STATUS_APPLICANT) {
            \humhub\modules\space\notifications\ApprovalRequestDeclined::instance()
                    ->from(Yii::$app->user->getIdentity())->about($this->owner)->send($user);
        }

        foreach (Membership::findAll(['user_id' => $userId, 'space_id' => $this->owner->id]) as $membership) {
            $membership->delete();
        }

        \humhub\modules\space\notifications\ApprovalRequest::instance()->from($user)->about($this->owner)->delete();

        \humhub\modules\space\notifications\Invite::instance()->from($this->owner)->delete($user);
    }

}
