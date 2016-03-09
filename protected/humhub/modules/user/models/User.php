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
use humhub\modules\user\models\GroupAdmin;
use humhub\modules\user\components\ActiveQueryUser;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $guid
 * @property integer $wall_id
 * @property integer $group_id
 * @property integer $status
 * @property integer $super_admin
 * @property string $username
 * @property string $email
 * @property string $auth_mode
 * @property string $tags
 * @property string $language
 * @property string $time_zone
 * @property string $last_activity_email
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $last_login
 * @property integer $visibility
 * @property integer $contentcontainer_id
 */
class User extends ContentContainerActiveRecord implements \yii\web\IdentityInterface, \humhub\modules\search\interfaces\Searchable
{

    /**
     * Authentication Modes
     */
    const AUTH_MODE_LDAP = "ldap";
    const AUTH_MODE_LOCAL = "local";

    /**
     * User Status Flags
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;
    const STATUS_NEED_APPROVAL = 2;

    /**
     * E-Mail Settings (Value Stored in UserSetting)
     */
    const RECEIVE_EMAIL_NEVER = 0;
    const RECEIVE_EMAIL_DAILY_SUMMARY = 1;
    const RECEIVE_EMAIL_WHEN_OFFLINE = 2;
    const RECEIVE_EMAIL_ALWAYS = 3;

    /**
     * Visibility Modes
     */
    const VISIBILITY_REGISTERED_ONLY = 1; // Only for registered members
    const VISIBILITY_ALL = 2; // Visible for all (also guests)

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
        return [
            [['username', 'email'], 'required'],
            [['wall_id', 'group_id', 'status', 'super_admin', 'created_by', 'updated_by', 'visibility'], 'integer'],
            [['tags'], 'string'],
            [['last_activity_email', 'created_at', 'updated_at', 'last_login'], 'safe'],
            [['guid'], 'string', 'max' => 45],
            [['username'], 'string', 'max' => 25, 'min' => Yii::$app->params['user']['minUsernameLength']],
            [['time_zone'], 'in', 'range' => \DateTimeZone::listIdentifiers()],
            [['email'], 'string', 'max' => 100],
            [['auth_mode'], 'string', 'max' => 10],
            [['language'], 'string', 'max' => 5],
            [['email'], 'unique'],
            [['username'], 'unique'],
            [['guid'], 'unique'],
            [['wall_id'], 'unique']
        ];
    }

    public function __get($name)
    {
        /**
         * Ensure there is always a related Profile Model also when it's
         * not really exists yet.
         */
        if ($name == 'profile') {
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
        $scenarios['editAdmin'] = ['username', 'email', 'group_id', 'super_admin', 'status'];
        $scenarios['registration'] = ['username', 'email', 'group_id'];
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
            'wall_id' => 'Wall ID',
            'group_id' => 'Group ID',
            'status' => 'Status',
            'super_admin' => 'Super Admin',
            'username' => 'Username',
            'email' => 'Email',
            'auth_mode' => 'Auth Mode',
            'tags' => 'Tags',
            'language' => 'Language',
            'last_activity_email' => 'Last Activity Email',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'last_login' => 'Last Login',
            'visibility' => 'Visibility',
        ];
    }

    public function behaviors()
    {
        return array(
            \humhub\components\behaviors\GUID::className(),
            \humhub\modules\user\behaviors\UserSetting::className(),
            \humhub\modules\user\behaviors\Followable::className(),
            \humhub\modules\user\behaviors\UserModelModules::className()
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
     * @return ActiveQueryContent
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

    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'group_id']);
    }
    
    public function isActive()
    {
        return $this->status === User::STATUS_ENABLED;
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
                throw new Exception("Tried to delete a user which is owner of a space!");
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
        GroupAdmin::deleteAll(['user_id' => $this->id]);
        Session::deleteAll(['user_id' => $this->id]);
        Setting::deleteAll(['user_id' => $this->id]);

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

            if ($this->auth_mode == "") {
                $this->auth_mode = self::AUTH_MODE_LOCAL;
            }

            if (\humhub\models\Setting::Get('allowGuestAccess', 'authentication_internal')) {
                // Set users profile default visibility to all
                if (\humhub\models\Setting::Get('defaultUserProfileVisibility', 'authentication_internal') == User::VISIBILITY_ALL) {
                    $this->visibility = User::VISIBILITY_ALL;
                }
            }

            $this->last_activity_email = new \yii\db\Expression('NOW()');

            // Set Status
            if ($this->status == "") {
                if (\humhub\models\Setting::Get('needApproval', 'authentication_internal')) {
                    $this->status = User::STATUS_NEED_APPROVAL;
                } else {
                    $this->status = User::STATUS_ENABLED;
                }
            }

            if ((\humhub\models\Setting::Get('defaultUserGroup', 'authentication_internal'))) {
                $this->group_id = \humhub\models\Setting::Get('defaultUserGroup', 'authentication_internal');
            }
        }

        if ($this->time_zone == "") {
            $this->time_zone = \humhub\models\Setting::Get('timeZone');
        }

        if ($this->group_id == "") {
            throw new \yii\base\Exception("Could not save user without group!");
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
        if ($this->status == User::STATUS_ENABLED) {
            Yii::$app->search->update($this);
        } else {
            Yii::$app->search->delete($this);
        }
        if ($insert) {
            if ($this->status == User::STATUS_ENABLED) {
                $this->setUpApproved();
            } else {
                $this->group->notifyAdminsForUserApproval($this);
            }
            $this->profile->user_id = $this->id;
        }

        if (Yii::$app->user->id == $this->id) {
            Yii::$app->user->setIdentity($this);
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

        // Auto Assign User to the Group Space
        $group = Group::findOne(['id' => $this->group_id]);
        if ($group != null && $group->space_id != "") {
            $space = \humhub\modules\space\models\Space::findOne(['id' => $group->space_id]);
            if ($space !== null) {
                $space->addMember($this->id);
            }
        }

        // Auto Add User to the default spaces
        foreach (\humhub\modules\space\models\Space::findAll(['auto_add_new_members' => 1]) as $space) {
            $space->addMember($this->id);
        }
    }

    /**
     * Returns users display name
     *
     * @return string
     */
    public function getDisplayName()
    {

        $name = '';

        $format = \humhub\models\Setting::Get('displayNameFormat');

        if ($this->profile !== null && $format == '{profile.firstname} {profile.lastname}')
            $name = $this->profile->firstname . " " . $this->profile->lastname;

        // Return always username as fallback
        if ($name == '' || $name == ' ')
            return $this->username;

        return $name;
    }

    /**
     * Checks if this records belongs to the current user
     *
     * @return boolean is current User
     */
    public function isCurrentUser()
    {
        if (Yii::$app->user->id == $this->id) {
            return true;
        }

        return false;
    }

    public function canAccessPrivateContent(User $user = null)
    {
        return ($this->isCurrentUser());
    }

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
     * Checks if given userId can write to this users wall
     *
     * @param type $userId
     * @return type
     */
    public function canWrite($userId = "")
    {

        if ($userId == "")
            $userId = Yii::$app->user->id;

        if ($userId == $this->id)
            return true;

        return false;
    }

    /**
     * Returns an array of informations used by search subsystem.
     * Function is defined in interface ISearchable
     *
     * @return Array
     */
    public function getSearchAttributes()
    {
        $attributes = array(
            'email' => $this->email,
            'username' => $this->username,
            'tags' => $this->tags,
            'firstname' => $this->profile->firstname,
            'lastname' => $this->profile->lastname,
            'title' => $this->profile->title,
            'groupId' => $this->group_id,
        );

        if (!$this->profile->isNewRecord) {
            foreach ($this->profile->getProfileFields() as $profileField) {
                $attributes['profile_' . $profileField->internal_name] = $profileField->getUserValue($this, true);
            }
        }

        $this->trigger(self::EVENT_SEARCH_ADD, new \humhub\modules\search\events\SearchAddEvent($attributes));

        return $attributes;
    }

    public function createUrl($route = null, $params = array(), $scheme = false)
    {
        if ($route === null) {
            $route = '/user/profile';
        }

        array_unshift($params, $route);
        if (!isset($params['uguid'])) {
            $params['uguid'] = $this->guid;
        }

        return \yii\helpers\Url::toRoute($params, $scheme);
    }

    /**
     *
     * @return type
     */
    public function getSpaces()
    {

        // TODO: SHOW ONLY REAL MEMBERSHIPS
        return $this->hasMany(\humhub\modules\space\models\Space::className(), ['id' => 'space_id'])
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
        if ($this->super_admin == 1) {
            return true;
        }

        if (GroupAdmin::find()->where(['user_id' => $this->id])->count() != 0) {
            return true;
        }

        return false;
    }

}
