<?php

namespace humhub\modules\space\models\forms;

use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\space\jobs\AddUsersToSpaceJob;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Model;
use yii\validators\EmailValidator;

/**
 * @author Luke
 * @package humhub.modules_core.space.forms
 * @since 0.5
 */
class InviteForm extends Model
{
    /**
     * Field for Invite GUIDs
     *
     * @var array
     */
    public $invite;

    /**
     * Field for external e-mails to invite
     *
     * @var string
     */
    public $inviteExternal;

    /**
     * Current Space
     *
     * @var Space
     */
    public $space;

    /**
     * Parsed Invites with User Objects
     *
     * @var array
     */
    public $invites = [];

    /**
     * @var int[] invite user ids
     */
    public $inviteIds = [];

    /**
     * Parsed list of E-Mails of field inviteExternal
     */
    public $invitesExternal = [];

    /**
     * Indicate for add users to space without invite notification
     * @var bool
     */
    public $withoutInvite = false;
    public $allRegisteredUsers = false;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        return [
            [['withoutInvite', 'allRegisteredUsers'], 'boolean'],
            ['invite', 'checkInvite'],
            ['inviteExternal', 'checkInviteExternal'],
        ];
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return [
            'invite' => Yii::t('SpaceModule.base', 'Invites'),
            'inviteExternal' => Yii::t('SpaceModule.base', 'New user by e-mail (comma separated)'),
        ];
    }

    /**
     * Saves the form and either directly invites the selected users directly or by queue if forceinvite or all registered
     * users are selected.
     *
     * @return bool true if save was successful otherwise false
     */
    public function save()
    {
        if(!$this->validate()) {
            return false;
        }

        if ($this->isQueuedJob()) {
            $this->forceInvite();
        } else {
            $this->inviteMembers();
        }

        $this->inviteExternal();

        return true;
    }

    /**
     * @return bool checks if user is allowed to add without invite
     */
    public function isQueuedJob()
    {
        // Pre-check if adding without invite / adding all members was requested
        if (!($this->withoutInvite || $this->allRegisteredUsers)) {
            return false;
        }

        // If user has permission to manage users, this action is allowed
        if (Yii::$app->user->can(ManageUsers::class)) {
            return true;
        }

        // Allow users to perform this action if this is allowed by config file
        // Pre-check if user is member of the space in question
        if (Yii::$app->getModule('space')->membersCanAddWithoutInvite === true) {
            $membership = Membership::findOne([
                'space_id' => $this->space->id,
                'user_id' => Yii::$app->user->identity->id,
            ]);

            if ($membership && $membership->status == Membership::STATUS_MEMBER) {
                return true;
            }
        }

        return false;
    }

    public function forceInvite()
    {
        Yii::$app->queue->push(new AddUsersToSpaceJob([
            'originatorId' => Yii::$app->user->identity->id,
            'forceMembership' => $this->withoutInvite,
            'spaceId' => $this->space->id,
            'userIds' => $this->getInviteIds(),
            'allUsers' => $this->allRegisteredUsers,
        ]));
    }

    /**
     * Invites selected members immediately
     *
     * @throws \yii\base\Exception
     */
    public function inviteMembers()
    {
        foreach ($this->getInvites() as $user) {
            $this->space->inviteMember($user->id, Yii::$app->user->id);
        }
    }

    /**
     * Invite external users by mail
     */
    public function inviteExternal()
    {
        if ($this->canInviteExternal()) {
            foreach ($this->getInvitesExternal() as $email) {
                $this->space->inviteMemberByEMail($email, Yii::$app->user->id);
            }
        }
    }

    /**
     * Checks if external user invitation setting is enabled
     *
     * @return int
     */
    public function canInviteExternal()
    {
        return Yii::$app->getModule('user')->settings->get('auth.internalUsersCanInvite');
    }

    /**
     * Add User to invite list
     *
     * @param User $user
     * @return bool true if user can be added to invite list
     */
    private function addUserToInviteList(User $user): bool
    {
        $membership = Membership::findOne([
            'space_id' => $this->space->id,
            'user_id' => $user->id,
            'status' => Membership::STATUS_MEMBER,
        ]);

        if ($membership) {
            return false;
        }

        $this->invites[] = $user;
        $this->inviteIds[] = $user->id;

        return true;
    }

    /**
     * Form Validator which checks the invite field
     *
     * @param string $attribute
     * @param array $params
     */
    public function checkInvite($attribute, $params)
    {
        // Check if email field is not empty
        if ($this->$attribute != '') {

            $invites = $this->$attribute;

            foreach ($invites as $userGuid) {
                $userGuid = preg_replace("/[^A-Za-z0-9\-]/", '', $userGuid);

                if ($userGuid == '') {
                    continue;
                }

                $user = User::findOne(['guid' => $userGuid]);

                if ($user === null) {
                    $this->addError($attribute, Yii::t('SpaceModule.base', 'User not found!'));
                    continue;
                }

                if (!$this->addUserToInviteList($user)) {
                    $this->addError(
                        $attribute,
                        Yii::t(
                            'SpaceModule.base',
                            "User '{username}' is already a member of this space!",
                            ['username' => $user->getDisplayName()]
                        )
                    );
                }
            }
        }
    }

    /**
     * Checks a comma separated list of e-mails which should invited to space.
     * E-Mails needs to be valid and not already registered.
     *
     * @param string $attribute
     * @param array $params
     */
    public function checkInviteExternal($attribute, $params)
    {

        // Check if email field is not empty
        if ($this->$attribute != '') {
            $emails = explode(",", $this->$attribute);

            // Loop over each given e-mail
            foreach ($emails as $email) {
                $email = trim($email);

                $validator = new EmailValidator();
                if (!$validator->validate($email)) {
                    $this->addError($attribute,
                        Yii::t('SpaceModule.base', "{email} is not valid!", ["{email}" => $email]));
                    continue;
                }

                $user = User::findOne(['email' => $email]);
                if ($user) {
                    $this->addUserToInviteList($user);
                } else {
                    $this->invitesExternal[] = $email;
                }
            }
        }
    }

    /**
     * @return User[] an Array with selected recipients
     */
    public function getInvites()
    {
        return $this->invites;
    }

    /**
     * @return int[] user invite ids
     */
    public function getInviteIds()
    {
        return $this->inviteIds;
    }

    /**
     * Returns an Array with selected recipients
     */
    public function getInvitesExternal()
    {
        return $this->invitesExternal;
    }

    /**
     * E-Mails entered in form
     *
     * @return array the emails
     */
    public function getEmails()
    {
        $emails = [];
        foreach (explode(',', $this->inviteExternal) as $email) {
            $emails[] = trim($email);
        }

        return $emails;
    }

}
