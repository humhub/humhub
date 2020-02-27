<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\components\behaviors\GUID;
use humhub\modules\admin\permissions\ManageGroups;
use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\content\components\behaviors\CompatModuleManager;
use humhub\modules\content\components\behaviors\SettingsBehavior;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\search\events\SearchAddEvent;
use humhub\modules\search\interfaces\Searchable;
use humhub\modules\search\jobs\DeleteDocument;
use humhub\modules\search\jobs\UpdateDocument;
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
use humhub\modules\user\widgets\UserWall;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $guid
 * @property integer $status
 * @property string $username
 * @property string $email
 * @property string $auth_mode
 * @property string $tags
 * @property string $language
 * @property string $time_zone
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $last_login
 * @property string $authclient_id
 * @property integer $visibility
 * @property integer $contentcontainer_id
 * @property Profile $profile
 *
 * @property string $displayName
 * @property string $displayNameSub
 */
class User extends ContentContainerActiveRecord implements IdentityInterface, Searchable
{

    /**
     * User Status Flags
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;
    const STATUS_NEED_APPROVAL = 2;
    const STATUS_SOFT_DELETED = 3;

    /**
     * Visibility Modes
     */
    const VISIBILITY_REGISTERED_ONLY = 1; // Only for registered members
    const VISIBILITY_ALL = 2; // Visible for all (also guests)

    /**
     * User Groups
     */
    const USERGROUP_SELF = 'u_self';
    const USERGROUP_FRIEND = 'u_friend';
    const USERGROUP_USER = 'u_user';
    const USERGROUP_GUEST = 'u_guest';

    /**
     * @event Event an event that is triggered when the user visibility is checked via [[isVisible()]].
     */
    const EVENT_CHECK_VISIBILITY = 'checkVisibility';

    /**
     * @event UserEvent an event that is triggered when the user is soft deleted (without contents) and also before complete deletion.
     */
    const EVENT_BEFORE_SOFT_DELETE = 'beforeSoftDelete';

    /**
     * A initial group for the user assigned while registration.
     * @var string|int
     */
    public $registrationGroupId = null;

    /**
     * @var boolean is system admin (cached)
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
        /* @var $userModule \humhub\modules\user\Module */
        $userModule = Yii::$app->getModule('user');

        return [
            [['username', 'email'], 'trim'],
            [['username'], 'required'],
            [['username'], 'unique'],
            [['username'], 'string', 'max' => $userModule->maximumUsernameLength, 'min' => $userModule->minimumUsernameLength],
            // Client validation is disable due to invalid client pattern validation
            [['username'], 'match', 'not' => true, 'pattern' => '/[\x00-\x1f\x7f]/', 'message' => Yii::t('UserModule.base', 'Username contains invalid characters.'), 'enableClientValidation' => false],
            [['status', 'created_by', 'updated_by', 'visibility'], 'integer'],
            [['tags'], 'string'],
            [['guid'], 'string', 'max' => 45],
            [['time_zone'], 'in', 'range' => \DateTimeZone::listIdentifiers()],
            [['auth_mode'], 'string', 'max' => 10],
            [['language'], 'string', 'max' => 5],
            [['email'], 'unique'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 150],
            [['email'], 'required', 'when' => function ($model, $attribute) use ($userModule) {
                return $userModule->emailRequired;
            }],
            [['guid'], 'unique'],
        ];
    }

    /**
     * Checks if user is system administrator
     *
     * @param boolean $cached Used cached result if available
     * @return boolean user is system admin
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
        } elseif ($name == 'profile') {
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

        return parent::__get($name);
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['login'] = ['username', 'password'];
        $scenarios['editAdmin'] = ['username', 'email', 'status'];
        $scenarios['registration_email'] = ['username', 'email'];
        $scenarios['registration'] = ['username'];

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
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            GUID::class,
            SettingsBehavior::class,
            Followable::class,
            CompatModuleManager::class
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
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
        return $this->guid;
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

    /**
     * Returns all GroupUser relations of this user as ActiveQuery
     * @return \yii\db\ActiveQuery
     */
    public function getGroupUsers()
    {
        return $this->hasMany(GroupUser::class, ['user_id' => 'id']);
    }

    /**
     * Returns all Group relations of this user as ActiveQuery
     * @return \yii\db\ActiveQuery
     */
    public function getGroups()
    {
        return $this->hasMany(Group::class, ['id' => 'group_id'])->via('groupUsers');
    }

    /**
     * Checks if the user has at least one group assigned.
     * @return boolean
     */
    public function hasGroup()
    {
        return $this->getGroups()->count() > 0;
    }

    /**
     * Returns all GroupUser relations this user is a manager of as ActiveQuery.
     * @return \yii\db\ActiveQuery
     */
    public function getManagerGroupsUser()
    {
        return $this->getGroupUsers()->where(['is_group_manager' => '1']);
    }

    /**
     * Returns all Groups this user is a maanger of as ActiveQuery.
     * @return \yii\db\ActiveQuery
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
     * @return \yii\db\ActiveQuery
     */
    public function getFriends()
    {
        if (Yii::$app->getModule('friendship')->getIsEnabled()) {
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
     * @since 1.2.3
     * @return boolean is visible
     */
    public function isVisible()
    {
        $event = new UserEvent(['user' => $this, 'result' => ['isVisible' => true]]);
        $this->trigger(self::EVENT_CHECK_VISIBILITY, $event);
        if ($event->result['isVisible'] && $this->isActive()) {
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
     * @since 1.3
     * @throws Exception
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

        Yii::$app->queue->push(new DeleteDocument([
            'activeRecordClass' => get_class($this),
            'primaryKey' => $this->id
        ]));

        // Cleanup related tables
        Invite::deleteAll(['user_originator_id' => $this->id]);
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
            'authclient_id' => new Expression('NULL')
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
                // Set users profile default visibility to all
                if (Yii::$app->getModule('user')->settings->get('auth.defaultUserProfileVisibility') == User::VISIBILITY_ALL) {
                    $this->visibility = User::VISIBILITY_ALL;
                }
            }

            if ($this->status == '') {
                $this->status = self::STATUS_ENABLED;
            }
        }

        if ($this->time_zone == '') {
            $this->time_zone = Yii::$app->settings->get('timeZone');
        }

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

        $this->updateSearch();

        if ($insert) {
            if ($this->status == User::STATUS_ENABLED) {
                $this->setUpApproved();
            } else {
                Group::notifyAdminsForUserApproval($this);
            }
            $this->profile->user_id = $this->id;
        }

        if (Yii::$app->user->id == $this->id) {
            Yii::$app->user->setIdentity($user);
        }

        parent::afterSave($insert, $changedAttributes);
    }


    /**
     * Update user record in search index
     *
     * If the user is not visible, the user will be removed from the search index.
     */
    public function updateSearch()
    {
        if ($this->isVisible()) {
            Yii::$app->queue->push(new UpdateDocument([
                'activeRecordClass' => get_class($this),
                'primaryKey' => $this->id
            ]));
        } else {
            Yii::$app->queue->push(new DeleteDocument([
                'activeRecordClass' => get_class($this),
                'primaryKey' => $this->id
            ]));
        }
    }

    public function setUpApproved()
    {
        $userInvite = Invite::findOne(['email' => $this->email]);

        if ($userInvite !== null) {
            // User was invited to a space
            if ($userInvite->source == Invite::SOURCE_INVITE) {
                $space = Space::findOne(['id' => $userInvite->space_invite_id]);
                if ($space != null) {
                    $space->addMember($this->id);
                }
            }

            // Delete/Cleanup Invite Entry
            $userInvite->delete();
        }

        // Auto Add User to the default spaces
        foreach (Space::findAll(['auto_add_new_members' => 1]) as $space) {
            $space->addMember($this->id);
        }
    }

    /**
     * Returns users display name
     *
     * @return string the users display name (e.g. firstname + lastname)
     */
    public function getDisplayName()
    {
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
    }

    /**
     * Returns the users display name sub text.
     * Per default as sub text the 'title' profile attribute is used
     *
     * @return string the display name sub text
     */
    public function getDisplayNameSub()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');

        if ($module->displayNameSubCallback !== null) {
            return call_user_func($module->displayNameSubCallback, $this);
        }

        if ($this->profile !== null && $this->profile->hasAttribute('title')) {
            return $this->profile->title;
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
     * Checks if the given $user instance shares the same identity with this
     * user instance.
     *
     * @param \humhub\modules\user\models\User $user
     * @return boolean
     */
    public function is(User $user = null)
    {
        if (!$user) {
            return false;
        }

        return $user->id === $this->id;
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
        if (Yii::$app->getModule('friendship')->getIsEnabled()) {
            return (Friendship::getStateForUser($this, $user) == Friendship::STATE_FRIENDS);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getWallOut()
    {
        return UserWall::widget(['user' => $this]);
    }

    /**
     * Checks if user has tags
     *
     * @return boolean has tags set
     */
    public function hasTags()
    {
        return ($this->tags != '');
    }

    /**
     * Returns an array with assigned Tags
     *
     * @return array tags
     */
    public function getTags()
    {
        return preg_split("/[;,#]+/", $this->tags);
    }

    /**
     * Returns an array of informations used by search subsystem.
     * Function is defined in interface ISearchable
     *
     * @return array
     */
    public function getSearchAttributes()
    {
        $attributes = [
            'email' => $this->email,
            'username' => $this->username,
            'tags' => $this->tags,
            'firstname' => $this->profile->firstname,
            'lastname' => $this->profile->lastname,
            'title' => $this->profile->title,
        ];

        // Add user group ids
        $groupIds = array_map(function ($group) {
            return $group->id;
        }, $this->groups);
        $attributes['groups'] = $groupIds;

        if (!$this->profile->isNewRecord) {
            foreach ($this->profile->getProfileFields() as $profileField) {
                if ($profileField->searchable) {
                    $attributes['profile_' . $profileField->internal_name] = $profileField->getUserValue($this, false);
                }
            }
        }

        $this->trigger(self::EVENT_SEARCH_ADD, new SearchAddEvent($attributes));

        return $attributes;
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
     * @return boolean
     * @throws \yii\base\InvalidConfigException
     */
    public function canApproveUsers()
    {
        if ($this->isSystemAdmin()) {
            return true;
        }

        if((new PermissionManager(['subject' => $this]))->can([ManageUsers::class, ManageGroups::class])) {
            return true;
        }

        return $this->getManagerGroups()->count() > 0;
    }

    /**
     * @return ActiveQuery
     */
    public function getAuths()
    {
        return $this->hasMany(Auth::class, ['user_id' => 'id']);
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

        if (Yii::$app->getModule('friendship')->getIsEnabled()) {
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

}
