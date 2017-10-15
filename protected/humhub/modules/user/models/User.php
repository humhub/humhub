<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use Yii;
use yii\base\Exception;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\events\UserEvent;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\space\models\Space;
use humhub\modules\content\models\Content;

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
 */
class User extends ContentContainerActiveRecord implements \yii\web\IdentityInterface, \humhub\modules\search\interfaces\Searchable
{

    /**
     * User Status Flags
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;
    const STATUS_NEED_APPROVAL = 2;

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
     * A initial group for the user assigned while registration.
     * @var type
     */
    public $registrationGroupId = null;

    /**
     * @var boolean is system admin (cached)
     */
    private $_isSystemAdmin = null;

    /**
     * @inheritdoc
     */
    public $controllerBehavior = \humhub\modules\user\behaviors\ProfileController::class;
    /**
     *
     * @var type 
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
            [['username'], 'required'],
            [['status', 'created_by', 'updated_by', 'visibility'], 'integer'],
            [['status', 'visibility'], 'integer'],
            [['tags'], 'string'],
            [['guid'], 'string', 'max' => 45],
            [['username'], 'string', 'max' => 50, 'min' => $userModule->minimumUsernameLength],
            [['time_zone'], 'in', 'range' => \DateTimeZone::listIdentifiers()],
            [['auth_mode'], 'string', 'max' => 10],
            [['language'], 'string', 'max' => 5],
            [['email'], 'unique'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 100],
            [['email'], 'required', 'when' => function($model, $attribute) use ($userModule) {
                    return $userModule->emailRequired;
                }],
            [['username'], 'unique'],
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
        } else if ($name == 'profile') {
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
            'status' => Yii::t('UserModule.models_User', 'Status'),
            'username' => Yii::t('UserModule.models_User', 'Username'),
            'email' => Yii::t('UserModule.models_User', 'Email'),
            'profile.firstname' => Yii::t('UserModule.models_Profile', 'First name'),
            'profile.lastname' => Yii::t('UserModule.models_Profile', 'Last name'),
            'auth_mode' => Yii::t('UserModule.models_User', 'Auth Mode'),
            'tags' => Yii::t('UserModule.models_User', 'Tags'),
            'language' => Yii::t('UserModule.models_User', 'Language'),
            'created_at' => Yii::t('UserModule.models_User', 'Created at'),
            'created_by' => Yii::t('UserModule.models_User', 'Created by'),
            'updated_at' => Yii::t('UserModule.models_User', 'Updated at'),
            'updated_by' => Yii::t('UserModule.models_User', 'Updated by'),
            'last_login' => Yii::t('UserModule.models_User', 'Last Login'),
            'visibility' => Yii::t('UserModule.models_User', 'Visibility'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array(
            \humhub\components\behaviors\GUID::class,
            \humhub\modules\content\components\behaviors\SettingsBehavior::class,
            \humhub\modules\user\behaviors\Followable::class,
            \humhub\modules\contentcontainer\behaviors\CompatModuleManager::class
        );
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['guid' => $token]);
    }

    /**
     * @inheritdoc
     *
     * @return \humhub\modules\content\components\ActiveQueryContent
     */
    public static function find()
    {
        return Yii::createObject(ActiveQueryUser::className(), [get_called_class()]);
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
        return $this->hasOne(Password::className(), ['user_id' => 'id'])->orderBy('created_at DESC');
    }

    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'id']);
    }

    /**
     * Returns all GroupUser relations of this user as ActiveQuery
     * @return type
     */
    public function getGroupUsers()
    {
        return $this->hasMany(GroupUser::className(), ['user_id' => 'id']);
    }

    /**
     * Returns all Group relations of this user as ActiveQuery
     * @return ActiveQuery
     */
    public function getGroups()
    {
        return $this->hasMany(Group::className(), ['id' => 'group_id'])->via('groupUsers');
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
        return $this->hasMany(Group::className(), ['id' => 'group_id'])->via('groupUsers', function($query) {
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
        if (Yii::$app->getModule('friendship')->getIsEnabled()) {
            return \humhub\modules\friendship\models\Friendship::getFriendsQuery($this);
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

        // We don't allow deletion of users who owns a space - validate that
        foreach (\humhub\modules\space\models\Membership::GetUserSpaces($this->id) as $space) {
            if ($space->isSpaceOwner($this->id)) {
                throw new Exception('Tried to delete a user (' . $this->id . ') which is owner of a space!');
            }
        }

        // Disable all enabled modules
        foreach ($this->getAvailableModules() as $moduleId => $module) {
            if ($this->isModuleEnabled($moduleId)) {
                $this->disableModule($moduleId);
            }
        }

        // Delete profile image
        $this->getProfileImage()->delete();

        // Remove from search index
        Yii::$app->search->delete($this);

        // Cleanup related tables
        Invite::deleteAll(['user_originator_id' => $this->id]);
        Follow::deleteAll(['user_id' => $this->id]);
        Follow::deleteAll(['object_model' => $this->className(), 'object_id' => $this->id]);
        Password::deleteAll(['user_id' => $this->id]);
        Profile::deleteAll(['user_id' => $this->id]);
        GroupUser::deleteAll(['user_id' => $this->id]);
        Session::deleteAll(['user_id' => $this->id]);

        return parent::beforeDelete();
    }

    /**
     * Before Save Addons
     *
     * @return type
     */
    public function beforeSave($insert)
    {
        if ($insert) {

            if ($this->auth_mode == '') {
                $passwordAuth = new \humhub\modules\user\authclient\Password();
                $this->auth_mode = $passwordAuth->getId();
            }

            if (Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess')) {
                // Set users profile default visibility to all
                if (Yii::$app->getModule('user')->settings->get('auth.defaultUserProfileVisibility') == User::VISIBILITY_ALL) {
                    $this->visibility = User::VISIBILITY_ALL;
                }
            }

            if ($this->status == "") {
                $this->status = self::STATUS_ENABLED;
            }
        }

        if ($this->time_zone == "") {
            $this->time_zone = Yii::$app->settings->get('timeZone');
        }

        return parent::beforeSave($insert);
    }

    /**
     * After Save Addons
     *
     * @return type
     */
    public function afterSave($insert, $changedAttributes)
    {
        // Make sure we get an direct User model instance
        // (e.g. not UserEditForm) for search rebuild
        $user = User::findOne(['id' => $this->id]);

        if ($user->isVisible()) {
            Yii::$app->search->update($user);
        } else {
            Yii::$app->search->delete($user);
        }

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
        return parent::afterSave($insert, $changedAttributes);
    }

    public function setUpApproved()
    {
        $userInvite = Invite::findOne(['email' => $this->email]);

        if ($userInvite !== null) {
            // User was invited to a space
            if ($userInvite->source == Invite::SOURCE_INVITE) {
                $space = \humhub\modules\space\models\Space::findOne(['id' => $userInvite->space_invite_id]);
                if ($space != null) {
                    $space->addMember($this->id);
                }
            }

            // Delete/Cleanup Invite Entry
            $userInvite->delete();
        }

        // Auto Add User to the default spaces
        foreach (\humhub\modules\space\models\Space::findAll(['auto_add_new_members' => 1]) as $space) {
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
        if (Yii::$app->getModule('user')->displayNameCallback !== null) {
            return call_user_func(Yii::$app->getModule('user')->displayNameCallback, $this);
        }

        $name = '';

        $format = Yii::$app->settings->get('displayNameFormat');

        if ($this->profile !== null && $format == '{profile.firstname} {profile.lastname}')
            $name = $this->profile->firstname . " " . $this->profile->lastname;

        // Return always username as fallback
        if ($name == '' || $name == ' ')
            return $this->username;

        return $name;
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
    public function is(User $user)
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
        return \humhub\modules\user\widgets\UserWall::widget(['user' => $this]);
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
     * @return Array
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
        $groupIds = array_map(function($group) {
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

        $this->trigger(self::EVENT_SEARCH_ADD, new \humhub\modules\search\events\SearchAddEvent($attributes));

        return $attributes;
    }

    /**
     *
     * @return type
     */
    public function getSpaces()
    {

        // TODO: SHOW ONLY REAL MEMBERSHIPS
        return $this->hasMany(Space::className(), ['id' => 'space_id'])
                        ->viaTable('space_membership', ['user_id' => 'id']);
    }

    /**
     * @return type
     */
    public function getHttpSessions()
    {
        return $this->hasMany(\humhub\modules\user\models\Session::className(), ['user_id' => 'id']);
    }

    /**
     * User can approve other users
     *
     * @return boolean
     */
    public function canApproveUsers()
    {
        if ($this->isSystemAdmin()) {
            return true;
        }

        return $this->getManagerGroups()->count() > 0;
    }

    /**
     * @return type
     */
    public function getAuths()
    {
        return $this->hasMany(\humhub\modules\user\models\Auth::className(), ['user_id' => 'id']);
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
