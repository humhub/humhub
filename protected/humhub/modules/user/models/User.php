<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use DateTimeZone;
use humhub\components\behaviors\GUID;
use humhub\libs\UUIDValidator;
use humhub\modules\admin\Module as AdminModule;
use humhub\modules\admin\permissions\ManageAllContent;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\content\jobs\ReindexUserContent;
use humhub\modules\content\models\Content;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\space\helpers\MembershipHelper;
use humhub\modules\space\models\Space;
use humhub\modules\user\authclient\Password as PasswordAuth;
use humhub\modules\user\behaviors\Followable;
use humhub\modules\user\behaviors\ProfileController;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\events\UserEvent;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\Module;
use humhub\modules\user\services\PasswordRecoveryService;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $status
 * @property string $username
 * @property string $email
 * @property string $auth_mode
 * @property string $language
 * @property string $time_zone
 * @property string $last_login
 * @property string $authclient_id
 * @property string $auth_key
 * @property-read string $authKey
 * @property Auth[] $auths
 * @property Password $currentPassword
 * @property-read ActiveQuery|null $friends
 * @property-read Group[] $groups
 * @property-read ActiveQuery $groupUsers
 * @property-read Session[] $httpSessions
 * @property-read Group[] $managerGroups
 * @property-read GroupUser[] $managerGroupsUser
 * @property-write bool $mustChangePassword
 * @property-read User|null $originator
 * @property-read PasswordRecoveryService $passwordRecoveryService
 * @property Profile $profile
 * @property-read array $searchAttributes
 * @property-read Space[] $spaces
 * @mixin Followable
 */
class User extends ContentContainerActiveRecord implements IdentityInterface
{
    /**
     * User Status Flags
     */
    public const STATUS_DISABLED = 0;
    public const STATUS_ENABLED = 1;
    public const STATUS_NEED_APPROVAL = 2;
    public const STATUS_SOFT_DELETED = 3;

    /**
     * Visibility Modes
     */
    public const VISIBILITY_REGISTERED_ONLY = 1; // Only for registered members
    public const VISIBILITY_ALL = 2; // Visible for all (also guests)
    public const VISIBILITY_HIDDEN = 3; // Invisible

    /**
     * User Markdown Editor Modes
     */
    public const EDITOR_RICH_TEXT = 0;
    public const EDITOR_PLAIN = 1;

    /**
     * User Groups
     */
    public const USERGROUP_SELF = 'u_self';
    public const USERGROUP_FRIEND = 'u_friend';
    public const USERGROUP_USER = 'u_user';
    public const USERGROUP_GUEST = 'u_guest';

    /**
     * Scenarios
     */
    public const SCENARIO_EDIT_ADMIN = 'editAdmin';
    public const SCENARIO_LOGIN = 'login';
    public const SCENARIO_REGISTRATION = 'registration';
    public const SCENARIO_REGISTRATION_EMAIL = 'registration_email';
    public const SCENARIO_EDIT_ACCOUNT_SETTINGS = 'editAccountSettings';
    public const SCENARIO_APPROVE = 'approve';

    /**
     * @event Event an event that is triggered when the user visibility is checked via [[isVisible()]].
     */
    public const EVENT_CHECK_VISIBILITY = 'checkVisibility';

    /**
     * @event UserEvent an event that is triggered when the user is soft deleted (without contents) and also before complete deletion.
     */
    public const EVENT_BEFORE_SOFT_DELETE = 'beforeSoftDelete';

    /**
     * A initial group for the user assigned while registration.
     * @var string|int
     */
    public $registrationGroupId = null;

    /**
     * @var bool is system admin (cached)
     */
    private $_isSystemAdmin = null;

    /**
     * @inheritdoc
     */
    public $controllerBehavior = ProfileController::class;

    /**
     *
     * @var string
     */
    public $defaultRoute = '/user/profile';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        /* @var $userModule Module */
        $userModule = Yii::$app->getModule('user');

        $rules = [
            [['username', 'email'], 'trim'],
            [['username'], 'required'],
            [['username'], 'unique'],
            [['username'], 'string', 'max' => $userModule->maximumUsernameLength, 'min' => $userModule->minimumUsernameLength],
            // Client validation is disable due to invalid client pattern validation
            [['username'], 'match', 'pattern' => $userModule->validUsernameRegexp, 'message' => Yii::t('UserModule.base', 'Username contains invalid characters.'), 'enableClientValidation' => false, 'when' => function ($model, $attribute) {
                return $model->getAttribute($attribute) !== $model->getOldAttribute($attribute);
            }],
            [['created_by', 'updated_by'], 'integer'],
            [['status'], 'in', 'range' => array_keys(self::getStatusOptions()), 'on' => self::SCENARIO_EDIT_ADMIN],
            [['visibility'], 'in', 'range' => array_keys(self::getVisibilityOptions()), 'on' => self::SCENARIO_EDIT_ADMIN],
            [['tagsField', 'blockedUsersField'], 'safe'],
            [['guid'], UUIDValidator::class],
            [['guid'], 'unique'],
            [['time_zone'], 'validateTimeZone'],
            [['auth_mode'], 'string', 'max' => 10],
            [['language'], 'string', 'max' => 20],
            ['language', 'in', 'range' => array_keys(Yii::$app->i18n->getAllowedLanguages()), 'except' => self::SCENARIO_APPROVE],
            [['email'], 'unique'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 150],
            [['username'], 'validateForbiddenUsername', 'on' => [self::SCENARIO_REGISTRATION]],
        ];

        if ($this->isEmailRequired()) { // HForm does not support 'required' in combination with 'when'.
            $rules[] = [['email'], 'required'];
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function isEmailRequired(): bool
    {
        /* @var $userModule Module */
        $userModule = Yii::$app->getModule('user');
        return $userModule->emailRequired;
    }

    /**
     * @inheritdoc
     */
    public function isAttributeRequired($attribute)
    {
        if ($attribute === 'email') {
            return $this->isEmailRequired();
        }

        return parent::isAttributeRequired($attribute);
    }

    /**
     * Validate attribute username
     * @param string $attribute
     */
    public function validateForbiddenUsername($attribute, $params)
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        if (in_array(strtolower($this->$attribute), $module->forbiddenUsernames)) {
            $this->addError($attribute, Yii::t('UserModule.account', 'You cannot use this username.'));
        }
    }

    /**
     * Validate attribute time zone
     * Force time zone to NULL if browser's time zone cannot be found on server side
     *
     * @param string $attribute
     */
    public function validateTimeZone($attribute, $params)
    {
        if (!in_array($this->$attribute, DateTimeZone::listIdentifiers())) {
            $this->$attribute = null;
        }
    }

    /**
     * Checks if user is system administrator
     *
     * @param bool $cached Used cached result if available
     * @return bool user is system admin
     */
    public function isSystemAdmin($cached = true)
    {
        if ($this->_isSystemAdmin === null || !$cached) {
            $this->_isSystemAdmin = ($this->getGroups()->where(['is_admin_group' => '1'])->count() > 0);
        }

        return $this->_isSystemAdmin;
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if ($name == 'super_admin') {
            /**
             * Replacement for old super_admin flag version
             */
            return $this->isSystemAdmin();
        }

        if ($name == 'profile') {
            /**
             * Ensure there is always a related Profile Model also when it's
             * not really exists yet.
             */
            $profile = parent::__get('profile');
            if (!$this->isRelationPopulated('profile') || $profile === null) {
                $profile = new Profile();
                $profile->user_id = $this->id;
                $this->populateRelation('profile', $profile);
            }
            return $profile;
        }

        if ($name === 'time_zone' && empty(parent::__get($name))) {
            // Fall back to default time zone
            return Yii::$app->settings->get('defaultTimeZone', Yii::$app->timeZone);
        }

        return parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_LOGIN] = ['username', 'password'];
        $scenarios[self::SCENARIO_EDIT_ADMIN] = ['username', 'email', 'status', 'visibility', 'language', 'tagsField'];
        $scenarios[self::SCENARIO_EDIT_ACCOUNT_SETTINGS] = ['language', 'visibility', 'time_zone', 'tagsField', 'blockedUsersField'];
        $scenarios[self::SCENARIO_REGISTRATION_EMAIL] = ['username', 'email', 'time_zone'];
        $scenarios[self::SCENARIO_REGISTRATION] = ['username', 'time_zone'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'guid' => 'Guid',
            'status' => Yii::t('UserModule.base', 'Status'),
            'username' => Yii::t('UserModule.base', 'Username'),
            'email' => Yii::t('UserModule.base', 'Email'),
            'profile.firstname' => Yii::t('UserModule.profile', 'First name'),
            'profile.lastname' => Yii::t('UserModule.profile', 'Last name'),
            'auth_mode' => Yii::t('UserModule.base', 'Auth Mode'),
            'tags' => Yii::t('UserModule.base', 'Tags'),
            'language' => Yii::t('UserModule.base', 'Language'),
            'created_at' => Yii::t('UserModule.base', 'Created at'),
            'created_by' => Yii::t('UserModule.base', 'Created by'),
            'updated_at' => Yii::t('UserModule.base', 'Updated at'),
            'updated_by' => Yii::t('UserModule.base', 'Updated by'),
            'last_login' => Yii::t('UserModule.base', 'Last Login'),
            'visibility' => Yii::t('UserModule.base', 'Visibility'),
            'originator.username' => Yii::t('UserModule.base', 'Invited by'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            GUID::class,
            Followable::class,
        ];
    }

    public static function findIdentity($id)
    {
        return Yii::$app->runtimeCache->getOrSet(User::class . '#' . $id, function () use ($id) {
            return static::findOne(['id' => $id]);
        });
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['guid' => $token]);
    }

    /**
     * @inheritdoc
     *
     * @return ActiveQueryUser
     */
    public static function find()
    {
        return Yii::createObject(ActiveQueryUser::class, [get_called_class()]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return empty($this->auth_key) ? $this->guid : $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function getCurrentPassword()
    {
        return $this->hasOne(Password::class, ['user_id' => 'id'])->orderBy('created_at DESC');
    }

    public function getProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'id']);
    }

    public function getOriginator()
    {
        return $this->hasOne(User::class, ['id' => 'user_originator_id'])->viaTable(Invite::tableName(), ['email' => 'email']);
    }

    /**
     * Returns all GroupUser relations of this user as ActiveQuery
     * @return ActiveQuery
     */
    public function getGroupUsers()
    {
        return $this->hasMany(GroupUser::class, ['user_id' => 'id']);
    }

    /**
     * Returns all Group relations of this user as ActiveQuery
     * @return ActiveQuery
     */
    public function getGroups()
    {
        return $this->hasMany(Group::class, ['id' => 'group_id'])->via('groupUsers');
    }

    /**
     * Checks if the user has at least one group assigned.
     * @return bool
     */
    public function hasGroup()
    {
        return $this->getGroups()->count() > 0;
    }

    /**
     * Returns all GroupUser relations this user is a manager of as ActiveQuery.
     * @return ActiveQuery
     */
    public function getManagerGroupsUser()
    {
        return $this->getGroupUsers()->where(['is_group_manager' => '1']);
    }

    /**
     * Returns all Groups this user is a maanger of as ActiveQuery.
     * @return ActiveQuery
     */
    public function getManagerGroups()
    {
        return $this->hasMany(Group::class, ['id' => 'group_id'])
            ->via('groupUsers', function ($query) {
                $query->andWhere(['is_group_manager' => '1']);
            });
    }

    /**
     * Returns all user this user is related as friend as ActiveQuery.
     * Returns null if the friendship module is deactivated.
     * @return ActiveQuery
     */
    public function getFriends()
    {
        if (Yii::$app->getModule('friendship')->isFriendshipEnabled()) {
            return Friendship::getFriendsQuery($this);
        }

        return null;
    }

    /**
     * @return bool true if the user status is enabled else false
     */
    public function isActive()
    {
        return $this->status === User::STATUS_ENABLED;
    }

    /**
     * Specifies whether the user should appear in user lists or in the search.
     *
     * @return bool is visible
     * @since 1.2.3
     */
    public function isVisible()
    {
        $event = new UserEvent(['user' => $this, 'result' => ['isVisible' => true]]);
        $this->trigger(self::EVENT_CHECK_VISIBILITY, $event);
        if ($event->result['isVisible'] && $this->isActive() && $this->visibility !== self::VISIBILITY_HIDDEN) {
            return true;
        }

        return false;
    }

    /**
     * Before Delete of a User
     *
     */
    public function beforeDelete()
    {
        $this->softDelete();

        if ($this->profile !== null) {
            $this->profile->delete();
        }

        return parent::beforeDelete();
    }

    /**
     *
     * @throws Exception
     * @since 1.3
     */
    public function softDelete()
    {
        // Delete spaces which are owned by this user.
        foreach (MembershipHelper::getOwnSpaces($this, false) as $space) {
            $space->delete();
        }

        $this->trigger(self::EVENT_BEFORE_SOFT_DELETE, new UserEvent(['user' => $this]));

        if ($this->profile !== null) {
            $this->profile->softDelete();
        }
        $this->getProfileImage()->delete();
        $this->getProfileBannerImage()->delete();

        foreach ($this->moduleManager->getEnabled() as $module) {
            $this->moduleManager->disable($module);
        }

        // Cleanup related tables
        Invite::deleteAll(['user_originator_id' => $this->id]);
        Invite::deleteAll(['email' => $this->email]);
        Follow::deleteAll(['user_id' => $this->id]);
        Follow::deleteAll(['object_model' => static::class, 'object_id' => $this->id]);
        Password::deleteAll(['user_id' => $this->id]);
        GroupUser::deleteAll(['user_id' => $this->id]);
        Session::deleteAll(['user_id' => $this->id]);
        Friendship::deleteAll(['user_id' => $this->id]);
        Friendship::deleteAll(['friend_user_id' => $this->id]);
        Auth::deleteAll(['user_id' => $this->id]);

        $this->updateAttributes([
            'email' => new Expression('NULL'),
            'username' => 'deleted-' . $this->id,
            'status' => User::STATUS_SOFT_DELETED,
            'authclient_id' => new Expression('NULL'),
        ]);

        return true;
    }

    /**
     * Before Save Addons
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            if ($this->auth_mode == '') {
                $passwordAuth = new PasswordAuth();
                $this->auth_mode = $passwordAuth->getId();
            }

            if (AuthHelper::isGuestAccessEnabled()) {
                // Set user profile default visibility
                $defaultUserProfileVisibility = Yii::$app->getModule('user')->settings->get('auth.defaultUserProfileVisibility');
                if (array_key_exists($defaultUserProfileVisibility, User::getVisibilityOptions())) {
                    $this->visibility = $defaultUserProfileVisibility;
                }
            }

            if ($this->status == '') {
                $this->status = self::STATUS_ENABLED;
            }
        }

        if (empty($this->time_zone)) {
            $this->time_zone = Yii::$app->settings->get('defaultTimeZone');
        }

        $this->email = empty($this->email) ? null : strtolower($this->email);

        return parent::beforeSave($insert);
    }

    /**
     * After Save Addons
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Make sure we get an direct User model instance
        // (e.g. not UserEditForm) for search rebuild
        $user = User::findOne(['id' => $this->id]);

        if ($insert) {
            if ($this->status == User::STATUS_NEED_APPROVAL) {
                Group::notifyAdminsForUserApproval($this);
            }
            $this->profile->user_id = $this->id;
        }

        // Reindex user content when status is changed to/from Enabled
        if (!$insert && isset($changedAttributes['status'])
            && ($this->status === User::STATUS_ENABLED || $changedAttributes['status'] === User::STATUS_ENABLED)) {
            Yii::$app->queue->push(new ReindexUserContent(['userId' => $this->id]));
        }

        // Don't move this line under setUpApproved() because ContentContainer record should be created firstly
        parent::afterSave($insert, $changedAttributes);

        // When insert an "::STATUS_ENABLED" user or update a user from status "::STATUS_NEED_APPROVAL" to "::STATUS_ENABLED"
        if (
            $this->status == User::STATUS_ENABLED
            && (
                $insert
                || (isset($changedAttributes['status']) && $changedAttributes['status'] == User::STATUS_NEED_APPROVAL)
            )
        ) {
            $this->setUpApproved();
        }

        if (Yii::$app->user->id == $this->id) {
            Yii::$app->user->setIdentity($user);
        }
    }


    private function setUpApproved()
    {
        $userInvite = Invite::findOne(['email' => $this->email]);

        if ($userInvite !== null) {
            // User was invited to a space
            if (in_array($userInvite->source, [Invite::SOURCE_INVITE, Invite::SOURCE_INVITE_BY_LINK], true)) {
                $space = $userInvite->space;
                if ($space !== null) {
                    $space->addMember($this->id);
                    Yii::$app->user->setReturnUrl($space->createUrl());
                }
            }

            // Delete/Cleanup Invite Entry
            $userInvite->delete();
        }

        // Auto Add User to the default spaces
        foreach (Space::findAll(['auto_add_new_members' => 1]) as $space) {
            $space->addMember($this->id);
        }

        /* @var $userModule Module */
        $userModule = Yii::$app->getModule('user');

        // Add User to the default group if no yet
        if (!$this->hasGroup() && ($defaultGroup = $userModule->getDefaultGroup())) {
            $defaultGroup->addUser($this);
        }
    }


    /**
     * Returns users display name
     *
     * @return string the users display name (e.g. firstname + lastname)
     */
    public function getDisplayName(): string
    {
        return Yii::$app->runtimeCache->getOrSet(__METHOD__ . $this->id, function () {
            /** @var Module $module */
            $module = Yii::$app->getModule('user');

            if ($module->displayNameCallback !== null) {
                return call_user_func($module->displayNameCallback, $this);
            }

            $name = '';

            $format = Yii::$app->settings->get('displayNameFormat');

            if ($this->profile !== null && $format == '{profile.firstname} {profile.lastname}') {
                $name = $this->profile->firstname . ' ' . $this->profile->lastname;
            }

            // Return always username as fallback
            if ($name == '' || $name == ' ') {
                return $this->username;
            }

            return $name;
        });
    }

    /**
     * Returns the users display name sub text.
     * Per default as sub text the 'title' profile attribute is used
     *
     * @return string the display name sub text
     */
    public function getDisplayNameSub(): string
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        if ($module->displayNameSubCallback !== null) {
            return call_user_func($module->displayNameSubCallback, $this);
        }

        if ($this->profile !== null) {
            return $this->profile->getFieldValue(Yii::$app->settings->get('displayNameSubFormat', ''), false, false) ?? '';
        }

        return '';
    }


    /**
     * Checks if this user is the current logged in user.
     * @inheritdoc
     */
    public function isCurrentUser()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        return $this->is(Yii::$app->user->getIdentity());
    }

    /**
     * @inheritdoc
     */
    public function canAccessPrivateContent(User $user = null)
    {
        $user = !$user && !Yii::$app->user->isGuest ? Yii::$app->user->getIdentity() : $user;

        // Guest
        if (!$user) {
            return false;
        }

        // Self
        if ($user->is($this)) {
            return true;
        }

        // Friend
        if (Yii::$app->getModule('friendship')->isFriendshipEnabled()) {
            return (Friendship::getStateForUser($this, $user) == Friendship::STATE_FRIENDS);
        }

        return false;
    }

    /**
     * Checks if the user is allowed to view all content
     *
     * @param string|null $containerClass class name of the content container
     * @return bool
     * @throws InvalidConfigException
     * @deprecated since 1.17 use canManageAllContent() instead
     * @since 1.8
     */
    public function canViewAllContent(?string $containerClass = null): bool
    {
        /** @var \humhub\modules\content\Module $module */
        $module = Yii::$app->getModule('content');

        return $module->adminCanViewAllContent && (
            $this->isSystemAdmin()
                || ($containerClass === Space::class && (new PermissionManager(['subject' => $this]))->can(ManageSpaces::class))
                || ($containerClass === static::class && (new PermissionManager(['subject' => $this]))->can(ManageUsers::class))
        );
    }

    /**
     * Checks if the user is allowed to manage all content
     *
     * @return bool
     * @throws InvalidConfigException
     * @since 1.17
     */
    public function canManageAllContent(): bool
    {
        /** @var AdminModule $module */
        $module = Yii::$app->getModule('admin');

        return
            $module->enableManageAllContentPermission
            && (new PermissionManager(['subject' => $this]))->can(ManageAllContent::class);
    }


    /**
     *
     * @return ActiveQuery
     */
    public function getSpaces()
    {
        // TODO: SHOW ONLY REAL MEMBERSHIPS
        return $this->hasMany(Space::class, ['id' => 'space_id'])
            ->viaTable('space_membership', ['user_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getHttpSessions()
    {
        return $this->hasMany(Session::class, ['user_id' => 'id']);
    }

    /**
     * User can approve other users
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function canApproveUsers()
    {
        if ($this->isSystemAdmin()) {
            return true;
        }

        if ((new PermissionManager(['subject' => $this]))->can([ManageUsers::class, ManageGroups::class])) {
            return true;
        }

        return $this->getManagerGroups()->count() > 0;
    }

    /**
     * Determines if this user can impersonate the given user.
     *
     * @param self $user
     * @return bool
     * @since 1.10
     */
    public function canImpersonate(self $user): bool
    {
        /* @var AdminModule $adminModule */
        $adminModule = Yii::$app->getModule('admin');
        if (!$adminModule->allowUserImpersonate) {
            return false;
        }

        if ($user->id == $this->id) {
            return false;
        }

        return (new PermissionManager(['subject' => $this]))->can(ManageUsers::class);
    }

    /**
     * @return ActiveQuery
     */
    public function getAuths()
    {
        return $this->hasMany(Auth::class, ['user_id' => 'id']);
    }

    /**
     * Check if this user has at least one authentication or the authentication with requested type
     *
     * @param string|null $type
     * @return bool
     */
    public function hasAuth(?string $type = null): bool
    {
        $auths = $this->getAuths();

        if ($type === null) {
            return $auths->exists();
        }

        foreach ($auths->all() as $auth) {
            /* @var Auth $auth */
            if ($auth->source === $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return user groups
     *
     * @return array user groups
     */
    public static function getUserGroups()
    {
        $groups = [];

        if (Yii::$app->getModule('friendship')->isFriendshipEnabled()) {
            $groups[self::USERGROUP_FRIEND] = Yii::t('UserModule.account', 'Your friends');
            $groups[self::USERGROUP_USER] = Yii::t('UserModule.account', 'Other users');
        } else {
            $groups[self::USERGROUP_USER] = Yii::t('UserModule.account', 'Users');
        }

        // Add guest groups if enabled
        if (AuthHelper::isGuestAccessEnabled()) {
            $groups[self::USERGROUP_GUEST] = Yii::t('UserModule.account', 'Not registered users');
        }

        return $groups;
    }

    /**
     * TODO: deprecated
     * @inheritdoc
     */
    public function getUserGroup(User $user = null)
    {
        $user = !$user && !Yii::$app->user->isGuest ? Yii::$app->user->getIdentity() : $user;

        if (!$user) {
            return self::USERGROUP_GUEST;
        } elseif ($this->is($user)) {
            return self::USERGROUP_SELF;
        }

        if (Yii::$app->getModule('friendship')->isFriendshipEnabled()) {
            if (Friendship::getStateForUser($this, $user) === Friendship::STATE_FRIENDS) {
                return self::USERGROUP_FRIEND;
            }
        }

        return self::USERGROUP_USER;
    }

    public function getDefaultContentVisibility()
    {
        // TODO: Implement same logic as for Spaces
        return Content::VISIBILITY_PUBLIC;
    }

    /**
     * @inheritdoc
     */
    public function getSettings(): ContentContainerSettingsManager
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('user');
        return $module->settings->contentContainer($this);
    }

    /**
     * Check if the User must change password
     *
     * @return bool
     * @since 1.8
     */
    public function mustChangePassword()
    {
        return !Yii::$app->user->isImpersonated && (bool)$this->getSettings()->get('mustChangePassword');
    }

    /**
     * Set/Unset User to force change password
     *
     * @param bool true - force user to change password, false - don't require to change password
     * @since 1.8
     */
    public function setMustChangePassword($state = true)
    {
        if ($state) {
            $this->getSettings()->set('mustChangePassword', true);
        } else {
            $this->getSettings()->delete('mustChangePassword');
        }
    }

    public static function getStatusOptions(bool $withDeleted = true): array
    {
        $options = [
            self::STATUS_ENABLED => Yii::t('AdminModule.user', 'Enabled'),
            self::STATUS_DISABLED => Yii::t('AdminModule.user', 'Disabled'),
            self::STATUS_NEED_APPROVAL => Yii::t('AdminModule.user', 'Unapproved'),
        ];

        if ($withDeleted) {
            $options[self::STATUS_SOFT_DELETED] = Yii::t('AdminModule.user', 'Deleted');
        }

        return $options;
    }

    public static function getVisibilityOptions($allowHidden = true): array
    {
        $options = [
            self::VISIBILITY_REGISTERED_ONLY => Yii::t('AdminModule.user', 'Visible for members only'),
        ];

        if (AuthHelper::isGuestAccessEnabled()) {
            $options[self::VISIBILITY_ALL] = Yii::t('AdminModule.user', 'Visible for members+guests');
        }

        if ($allowHidden) {
            $options[self::VISIBILITY_HIDDEN] = Yii::t('AdminModule.user', 'Invisible');
        }

        return $options;
    }

    public function getPasswordRecoveryService(): PasswordRecoveryService
    {
        return new PasswordRecoveryService($this);
    }
}
