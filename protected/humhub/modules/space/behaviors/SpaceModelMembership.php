<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\behaviors;

use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\space\activities\MemberAdded;
use humhub\modules\space\activities\MemberRemoved;
use humhub\modules\space\MemberEvent;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\notifications\ApprovalRequest;
use humhub\modules\space\notifications\ApprovalRequestAccepted;
use humhub\modules\space\notifications\ApprovalRequestDeclined;
use humhub\modules\space\notifications\Invite as InviteNotification;
use humhub\modules\space\notifications\InviteAccepted;
use humhub\modules\space\notifications\InviteDeclined;
use humhub\modules\space\notifications\InviteRevoked;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\User;
use Throwable;
use Yii;
use yii\base\Behavior;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\validators\EmailValidator;

/**
 * SpaceModelMemberBehavior bundles all membership related methods of the Space model.
 *
 * @property-read Space $owner
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 */
class SpaceModelMembership extends Behavior
{
    private $_spaceOwner = null;

    /**
     * Checks if given userId is Member of this Space.
     *
     * @param int $userId
     * @return bool
     */
    public function isMember($userId = '')
    {
        // Take current userid if none is given
        if ($userId == '' && !Yii::$app->user->isGuest) {
            $userId = Yii::$app->user->id;
        } elseif ($userId == '' && Yii::$app->user->isGuest) {
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
     * @param number $userId , if empty hte currently logged in user is taken.
     * @return bool
     */
    public function canLeave($userId = '')
    {
        // Take current userid if none is given
        if ($userId == '') {
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
     * @param User|int|null $user User instance or userId
     * @return bool
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
     * @param User|int|null $userId
     * @return bool
     */
    public function setSpaceOwner($user = null)
    {
        $userId = ($user instanceof User) ? $user->id : $user;

        if ($userId instanceof User) {
            $userId = $userId->id;
        } elseif (!$userId || $userId == 0) {
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
     * @return User
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
     * @return bool checks if the current user is allowed to delete this space
     * @since 1.3
     */
    public function canDelete()
    {
        return Yii::$app->user->isAdmin() || $this->isSpaceOwner();
    }

    /**
     * Is given User owner of this Space
     * @param User|int|null $userId
     * @return bool
     */
    public function isSpaceOwner($userId = null)
    {
        if (empty($userId) && Yii::$app->user->isGuest) {
            return false;
        } elseif ($userId instanceof User) {
            $userId = $userId->id;
        } elseif (empty($userId)) {
            $userId = Yii::$app->user->id;
        }

        return $this->owner->created_by == $userId;
    }

    /**
     * Sets Owner for this workspace
     *
     * @param int $userId
     * @return bool
     */
    public function setAdmin($userId = null)
    {
        if ($userId instanceof User) {
            $userId = $userId->id;
        } elseif (!$userId || $userId == 0) {
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
     *
     * @return Membership|null the membership
     */
    public function getMembership($userId = null): ?Membership
    {
        return Membership::findMembership($this->owner->id, $userId);
    }

    /**
     * Invites a not registered member to this space
     *
     * @param string $email
     * @param int $originatorUserId
     */
    public function inviteMemberByEMail($email, $originatorUserId)
    {
        // Invalid E-Mail
        $validator = new EmailValidator();
        if (!$validator->validate($email)) {
            return false;
        }

        // User already registered
        $user = User::findOne(['email' => $email]);
        if ($user != null) {
            return false;
        }

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
            // Steal it and send mail again
            // Unfortunately there are no multiple workspace invites supported
            // So we take the last one
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
     * @param int $userId
     * @param string $message
     */
    public function requestMembership($userId, $message = '')
    {
        $user = ($userId instanceof User) ? $userId : User::findOne(['id' => $userId]);

        // Add Membership
        $membership = new Membership([
            'space_id' => $this->owner->id,
            'user_id' => $user->id,
            'status' => Membership::STATUS_APPLICANT,
            'group_id' => Space::USERGROUP_MEMBER,
            'request_message' => $message,
        ]);

        $membership->save();

        ApprovalRequest::instance()->from($user)->about($this->owner)->withMessage($message)->sendBulk($this->getAdminsQuery());
    }

    /**
     * Returns the admins of the space
     *
     * @return User[] the admin users of the space
     */
    public function getAdmins()
    {
        return $this->getAdminsQuery()->all();
    }

    /**
     * Returns user query for admins of the space
     *
     * @return ActiveQueryUser
     * @since 1.3
     */
    public function getAdminsQuery(): ActiveQueryUser
    {
        return $this->owner->getMemberListService()
            ->getAdminQuery()
            ->andWhere(['space_membership.group_id' => Space::USERGROUP_ADMIN]);
    }

    /**
     * Invites a registered user to this space
     *
     * If user is already invited, retrigger invitation.
     * If user is applicant approve it.
     *
     * @param int $userId
     * @param int $originatorId
     * @param bool $sendInviteNotification
     */
    public function inviteMember($userId, $originatorId, $sendInviteNotification = true)
    {
        $membership = $this->getMembership($userId);

        if ($membership != null) {
            switch ($membership->status) {
                case Membership::STATUS_APPLICANT:
                    // If user is an applicant of this space add user and return.
                    $this->addMember($userId);
                    // no break
                case Membership::STATUS_MEMBER:
                    // If user is already a member just ignore the invitation.
                    return;
                case Membership::STATUS_INVITED:
                    // If user is already invited, remove old invite notification and retrigger
                    $oldNotification = new InviteNotification(['source' => $this->owner]);
                    $oldNotification->delete(User::findOne(['id' => $userId]));
                    break;
            }
        } else {
            $membership = new Membership([
                'space_id' => $this->owner->id,
                'user_id' => $userId,
                'status' => Membership::STATUS_INVITED,
                'group_id' => Space::USERGROUP_MEMBER,
            ]);
        }

        // Update or set originator
        $membership->originator_user_id = $originatorId;

        if (!$membership->save()) {
            throw new Exception('Could not save membership!' . print_r($membership->getErrors(), 1));
        }

        if ($sendInviteNotification) {
            $this->sendInviteNotification($userId, $originatorId);
        }
    }

    /**
     * Sends an Invite Notification to the given user.
     *
     * @param int $userId
     * @param int $originatorId
     */
    protected function sendInviteNotification($userId, $originatorId)
    {
        $notification = new InviteNotification([
            'source' => $this->owner,
            'originator' => User::findOne(['id' => $originatorId]),
        ]);

        $notification->send(User::findOne(['id' => $userId]));
    }

    /**
     * Adds an member to this space.
     *
     * This can happens after an clicking "Request Membership" Link
     * after Approval or accepting an invite.
     *
     * @param int $userId
     * @param int $canLeave 0: user cannot cancel membership | 1: can cancel membership | 2: depending on space flag members_can_leave
     * @param bool $silent add member without any notifications
     * @param bool $showAtDashboard add member without any notifications
     * @param string $groupId
     * @return bool
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function addMember(
        int    $userId,
        int    $canLeave = 1,
        bool   $silent = false,
        string $groupId = Space::USERGROUP_MEMBER,
        bool   $showAtDashboard = true,
    ): bool {
        $user = User::findOne(['id' => $userId]);
        if (!$user) {
            return false;
        }

        $membership = $this->getMembership($userId);

        if ($membership === null) {
            // Add Membership
            $membership = new Membership([
                'space_id' => $this->owner->id,
                'user_id' => $userId,
                'status' => Membership::STATUS_MEMBER,
                'group_id' => $groupId,
                'show_at_dashboard' => $showAtDashboard,
                'can_cancel_membership' => $canLeave,
            ]);

            $userInvite = Invite::findOne(['email' => $user->email]);

            if ($userInvite !== null
                && !empty($userInvite->user_originator_id)
                && $userInvite->source == Invite::SOURCE_INVITE && !$silent) {
                $originator = User::findOne(['id' => $userInvite->user_originator_id]);
                if ($originator !== null) {
                    InviteAccepted::instance()->from($user)->about($this->owner)->send($originator);
                }
            }
        } else {
            // User is already member
            if ($membership->status == Membership::STATUS_MEMBER) {
                return true;
            }

            // User requested membership
            if ($membership->status == Membership::STATUS_APPLICANT && !$silent) {
                ApprovalRequestAccepted::instance()
                    ->from(Yii::$app->user->getIdentity())->about($this->owner)->send($user);
            }

            // User was invited
            if ($membership->status == Membership::STATUS_INVITED && !$silent) {
                InviteAccepted::instance()->from($user)->about($this->owner)
                    ->send(User::findOne(['id' => $membership->originator_user_id]));
            }

            // Update Membership
            $membership->status = Membership::STATUS_MEMBER;
            $membership->group_id = $groupId;
        }

        if (!$membership->save()) {
            return false;
        }

        MemberEvent::trigger(Membership::class, Membership::EVENT_MEMBER_ADDED, new MemberEvent([
            'space' => $this->owner, 'user' => $user,
        ]));

        if (!$silent && !$this->owner->settings->get('hideMembers')) {
            // Create Activity
            MemberAdded::instance()->from($user)->about($this->owner)->save();
        }

        // Members can't also follow the space
        $this->owner->unfollow($userId);

        // Delete invite notification for this user
        InviteNotification::instance()->about($this->owner)->delete($user);

        // Delete pending approval request notifications for this user
        ApprovalRequest::instance()->from($user)->about($this->owner)->delete();

        return true;
    }

    /**
     * Remove Membership
     *
     * @param int|null $userId of User to Remove
     * @return bool
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function removeMember($userId = null)
    {
        if (!$userId) {
            $userId = Yii::$app->user->id;
        }

        $user = User::findOne(['id' => $userId]);
        $membership = $this->getMembership($userId);

        if (!$membership) {
            return true;
        }

        if ($this->isSpaceOwner($userId)) {
            return false;
        }

        Membership::getDb()->transaction(function ($db) use ($membership, $user) {
            foreach (Membership::findAll(['user_id' => $user->id, 'space_id' => $this->owner->id]) as $obsoleteMembership) {
                $obsoleteMembership->delete();
            }

            $this->handleRemoveMembershipEvent($membership, $user);
        });

        return true;
    }

    /**
     * Responsible for event,activity and notification handling in case of a space membership removal.
     *
     * @param Membership $membership
     * @param User $user
     * @throws Exception
     * @throws Throwable
     * @throws InvalidConfigException
     */
    private function handleRemoveMembershipEvent(Membership $membership, User $user)
    {
        Membership::unsetCache($this->owner->id, $user->id);

        // Get rid of old notifications
        ApprovalRequest::instance()->from($user)->about($this->owner)->delete();
        InviteNotification::instance()->about($this->owner)->delete($user);

        switch ($membership->status) {
            case Membership::STATUS_MEMBER:
                return $this->handleCancelMemberEvent($user);
            case Membership::STATUS_INVITED:
                return $this->handleCancelInvitationEvent($membership, $user);
            case Membership::STATUS_APPLICANT:
                return $this->handleCancelApplicantEvent($membership, $user);
        }
    }

    /**
     * @param User $user
     * @throws Exception
     */
    private function handleCancelMemberEvent(User $user)
    {
        if (!$this->owner->settings->get('hideMembers')) {
            MemberRemoved::instance()->about($this->owner)->from($user)->create();
        }

        MemberEvent::trigger(
            Membership::class,
            Membership::EVENT_MEMBER_REMOVED,
            new MemberEvent(['space' => $this->owner, 'user' => $user]),
        );
    }

    /**
     * Handles the cancellation of an invitation. An invitation can be declined by the invited user or canceled by a
     * space admin.
     *
     * @param Membership $membership
     * @param User $user
     * @throws InvalidConfigException
     */
    private function handleCancelInvitationEvent(Membership $membership, User $user)
    {
        if ($membership->originator && $membership->isCurrentUser()) {
            InviteDeclined::instance()->from(Yii::$app->user->identity)->about($this->owner)->send($membership->originator);
        } elseif (Yii::$app->user->identity) {
            InviteRevoked::instance()->from(Yii::$app->user->identity)->about($this->owner)->send($user);
        }
    }

    /**
     * Handles the cancellation of an space application. An application can be canceled by the applicant himself or
     * declined by an space admin.
     *
     * @param Membership $membership
     * @param User $user
     * @throws InvalidConfigException
     */
    private function handleCancelApplicantEvent(Membership $membership, User $user)
    {
        // Only send a declined notification if the user did not cancel the request himself.
        if (Yii::$app->user->identity && !$membership->isCurrentUser()) {
            ApprovalRequestDeclined::instance()->from(Yii::$app->user->identity)->about($this->owner)->send($user);
        }
    }

}
