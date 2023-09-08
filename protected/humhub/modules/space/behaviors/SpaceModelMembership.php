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
use Yii;
use yii\base\Behavior;
use yii\base\Exception;
use yii\validators\EmailValidator;

/**
 * SpaceModelMemberBehavior bundles all membership related methods of the Space model.
 *
 * @property-read Space $owner
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 */
class SpaceModelMembership extends Behavior
{
    private ?User $_spaceOwner = null;

    /**
     * Checks if given userId is a Member of this Space.
     *
     * @param User|int|string|null $user
     *
     * @return boolean
     */
    public function isMember($user = null): bool
    {
        // Take current userid if none is given
        $user = User::findInstance($user);

        if ($user === null && Yii::$app->user->isGuest) {
            return false;
        }

        $membership = $this->getMembership($user);

        if ($membership !== null && (int)$membership->status === Membership::STATUS_MEMBER) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a given Userid is allowed to leave this space.
     * A User is allowed to leave, if the can_cancel_membership flag in the space_membership table is 1. If it is 2, the decision is delegated to the space.
     *
     * @param User|int|string|null $user User to check for. If empty, the currently logged-in user is used.
     *
     * @return bool
     */
    public function canLeave($user = null): bool
    {
        $membership = $this->getMembership($user);

        if ($membership !== null && !empty($membership->can_cancel_membership)) {
            return $membership->can_cancel_membership === 1 || ($membership->can_cancel_membership === 2 && !empty($this->owner->members_can_leave));
        }

        return false;
    }

    /**
     * Checks if given Userid is Admin of this Space or has the permission to manage spaces.
     *
     * If no UserId is given, current UserId will be used
     *
     * @param User|int|string|null $user User instance or userId
     * @return boolean
     */
    public function isAdmin($user = null): bool
    {
        $user = User::findInstance($user);

        if ($user === null) {
            return Yii::$app->user->can(new ManageSpaces());
        }

        if ($this->isSpaceOwner($user)) {
            return true;
        }

        $membership = $this->getMembership($user);

        return ($membership && $membership->group_id == Space::USERGROUP_ADMIN && $membership->status == Membership::STATUS_MEMBER);
    }

    /**
     * Sets Owner for this workspace
     *
     * @param User|int|string|null $user
     * @return boolean
     */
    public function setSpaceOwner($user = null): bool
    {
        $user = User::findInstance($user);

        $this->setAdmin($user);

        $this->owner->created_by = $user->id;
        $this->owner->update(false, ['created_by']);

        $this->_spaceOwner = null;

        return true;
    }

    /**
     * Gets Owner for this workspace
     *
     * @return User|null
     */
    public function getSpaceOwner(): ?User
    {
        if ($this->_spaceOwner !== null) {
            return $this->_spaceOwner;
        }

        $this->_spaceOwner = User::findInstance($this->owner->created_by);

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
     * Is the given User owner of this Space
     *
     * @param User|int|string|null $user
     *
     * @return bool
     */
    public function isSpaceOwner($user = null): bool
    {
        $user = User::findInstanceAsId($user);

        if ($user === null && Yii::$app->user->isGuest) {
            return false;
        }

        return (int)$this->owner->created_by === $user;
    }

    /**
     * Sets Owner for this workspace
     *
     * @param User|int|string|null $user
     * @return boolean
     */
    public function setAdmin($user = null)
    {
        $membership = $this->getMembership($user);

        if ($membership !== null) {
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
        return Membership::findInstance([$this->owner->id, $userId]);
    }

    /**
     * Invites a not registered member to this space
     *
     * @param string $email
     * @param integer $originatorUserId
     */
    public function inviteMemberByEMail($email, $originatorUserId)
    {
        // Invalid E-Mail
        $validator = new EmailValidator();
        if (!$validator->validate($email)) {
            return false;
        }

        // User already registered
        $user = User::findInstance(['email' => $email]);
        if ($user != null) {
            return false;
        }

        $userInvite = Invite::findInstance($email);
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
     * @param User|int|string|null $user
     * @param string $message
     */
    public function requestMembership($user, $message = '')
    {
        $user = User::findInstance($user);

        // Add Membership
        $membership = new Membership([
            'space_id' => $this->owner->id,
            'user_id' => $user->id,
            'status' => Membership::STATUS_APPLICANT,
            'group_id' => Space::USERGROUP_MEMBER,
            'request_message' => $message
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
    public function getAdminsQuery()
    {
        $query = Membership::getSpaceMembersQuery($this->owner);
        $query->andWhere(['space_membership.group_id' => Space::USERGROUP_ADMIN]);

        return $query;
    }

    /**
     * Invites a registered user to this space
     *
     * If user is already invited, retrigger invitation.
     * If user is applicant approve it.
     *
     * @param integer $userId
     * @param integer $originatorId
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
                case Membership::STATUS_MEMBER:
                    // If user is already a member just ignore the invitation.
                    return;
                case Membership::STATUS_INVITED:
                    // If user is already invited, remove old invite notification and retrigger
                    $oldNotification = new InviteNotification(['source' => $this->owner]);
                    $oldNotification->delete(User::findInstance($userId));
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
     * @param integer $userId
     * @param integer $originatorId
     */
    protected function sendInviteNotification($userId, $originatorId)
    {
        $notification = new InviteNotification([
            'source' => $this->owner,
            'originator' => User::findInstance($originatorId)
        ]);

        $notification->send(User::findInstance($userId));
    }

    /**
     * Adds a member to this space.
     *
     * This can happen after clicking on a "Request Membership" Link
     * after Approval or accepting an invitation.
     *
     * @param User|int|string|null $user
     * @param int $canLeave 0: user cannot cancel membership | 1: can cancel membership | 2: depending on space flag members_can_leave
     * @param bool $silent add member without any notifications
     * @param bool $showAtDashboard add member without any notifications
     * @param string $groupId
     *
     * @return bool
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function addMember(
        $user,
        int $canLeave = 1,
        bool $silent = false,
        string $groupId = Space::USERGROUP_MEMBER,
        bool $showAtDashboard = true
    ): bool {
        $user = User::findInstance($user);
        if (!$user) {
            return false;
        }

        $membership = $this->getMembership($user);

        if ($membership === null) {
            // Add Membership
            $membership = new Membership([
                'space_id' => $this->owner->id,
                'user_id' => $user->id,
                'status' => Membership::STATUS_MEMBER,
                'group_id' => $groupId,
                'show_at_dashboard' => $showAtDashboard,
                'can_cancel_membership' => $canLeave
            ]);

            $userInvite = Invite::findInstance($user->email);

            if (
                $userInvite !== null &&
                !empty($userInvite->user_originator_id) &&
                $userInvite->source == Invite::SOURCE_INVITE && !$silent
            ) {
                $originator = User::findInstance($userInvite->user_originator_id);
                if ($originator !== null) {
                    InviteAccepted::instance()->from($user)->about($this->owner)->send($originator);
                }
            }
        } else {
            // User is already a member
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
                    ->send(User::findInstance($membership->originator_user_id));
            }

            // Update Membership
            $membership->status = Membership::STATUS_MEMBER;
            $membership->group_id = $groupId;
        }

        if (!$membership->save()) {
            return false;
        }

        MemberEvent::trigger(Membership::class, Membership::EVENT_MEMBER_ADDED, new MemberEvent([
            'space' => $this->owner, 'user' => $user
        ]));

        if (!$silent && !$this->owner->settings->get('hideMembers')) {
            // Create Activity
            MemberAdded::instance()->from($user)->about($this->owner)->save();
        }

        // Members can't also follow the space
        $this->owner->unfollow($user);

        // Delete invite notification for this user
        InviteNotification::instance()->about($this->owner)->delete($user);

        // Delete pending approval request notifications for this user
        ApprovalRequest::instance()->from($user)->about($this->owner)->delete();

        return true;
    }

    /**
     * Remove Membership
     *
     * @param integer $userId of User to Remove
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \Throwable
     */
    public function removeMember($userId = '')
    {
        if ($userId == '') {
            $userId = Yii::$app->user->id;
        }

        $user = User::findInstance($userId);
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
    }

    /**
     * Responsible for event,activity and notification handling in case of a space membership removal.
     *
     * @param Membership $membership
     * @param User $user
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    private function handleRemoveMembershipEvent(Membership $membership, User $user)
    {
        Membership::unsetCache([$this->owner->id, $user->id]);

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
            new MemberEvent(['space' => $this->owner, 'user' => $user])
        );
    }

    /**
     * Handles the cancellation of an invitation. An invitation can be declined by the invited user or canceled by a
     * space admin.
     *
     * @param Membership $membership
     * @param User $user
     * @throws \yii\base\InvalidConfigException
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
     * @throws \yii\base\InvalidConfigException
     */
    private function handleCancelApplicantEvent(Membership $membership, User $user)
    {
        // Only send a declined notification if the user did not cancel the request himself.
        if (Yii::$app->user->identity && !$membership->isCurrentUser()) {
            ApprovalRequestDeclined::instance()->from(Yii::$app->user->identity)->about($this->owner)->send($user);
        }
    }
}
