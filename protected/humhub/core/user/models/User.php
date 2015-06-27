<?php

namespace humhub\core\user\models;

use Yii;
use humhub\models\Setting;

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
 * @property string $last_activity_email
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $last_login
 * @property integer $visibility
 */
class User extends \humhub\core\content\components\activerecords\ContentContainer implements \yii\web\IdentityInterface, \humhub\core\search\interfaces\Searchable
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
            [['username', 'auth_mode', 'last_activity_email', 'group_id', 'email'], 'required'],
            [['wall_id', 'group_id', 'status', 'super_admin', 'created_by', 'updated_by', 'visibility'], 'integer'],
            [['tags'], 'string'],
            [['last_activity_email', 'created_at', 'updated_at', 'last_login'], 'safe'],
            [['guid'], 'string', 'max' => 45],
            [['username'], 'string', 'max' => 25],
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
            if (!$this->isRelationPopulated('profile')) {
                $profile = $this->getProfile()->findFor('profile', $this);
                if ($profile === null) {
                    $profile = new Profile();
                    $profile->user_id = $this->id;
                }
                $this->populateRelation('profile', $profile);
            }
        }
        return parent::__get($name);
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rulesOld()
    {

        if ($this->scenario == 'register') {
            // Only return all fields required for registration.
            // All other fields should be unsafe.
            return array(
                array('username, group_id, email', 'required'),
                array('username', 'unique', 'caseSensitive' => false, 'targetClass' => self::className()),
                array('email', 'email'),
                array('group_id', 'integer'),
                array('email', 'unique', 'caseSensitive' => false, 'targetClass' => self::className()),
                array('username', 'match', 'not' => true, 'pattern' => '/[^a-zA-Z0-9äöüÄÜÖß\+\-\._ ]/', 'message' => Yii::t('UserModule.models_User', 'Username can contain only letters, numbers, spaces and special characters (+-._)')),
                array('username', 'string', 'max' => 25, 'min' => 4),
            );
        }

        $rules = array();
        $rules[] = array('wall_id, status, group_id, super_admin, created_by, updated_by, visibility', 'integer', 'integerOnly' => true);
        $rules[] = array('email', 'email');
        $rules[] = array('guid', 'string', 'max' => 45);
        $rules[] = array('username', 'unique', 'targetClass' => self::className());
        $rules[] = array('email', 'unique', 'targetClass' => self::className());
        $rules[] = array('email,tags', 'string', 'max' => 100);
        $rules[] = array('username', 'string', 'max' => 25, 'min' => 4);
        $rules[] = array('language', 'string', 'max' => 5);
        $rules[] = array('language', 'match', 'not' => true, 'pattern' => '/[^a-zA-Z_]/', 'message' => Yii::t('UserModule.models_User', 'Invalid language!'));
        $rules[] = array('auth_mode, tags, created_at, updated_at, last_activity_email, last_login', 'safe');
        $rules[] = array('auth_mode', 'string', 'max' => 10);
        return $rules;
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['login'] = ['username', 'password'];
        $scenarios['editAdmin'] = ['username', 'email', 'group_id', 'super_admin', 'auth_mode', 'status'];
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
            \humhub\core\user\behaviors\UserSetting::className(),
            \humhub\core\user\behaviors\Followable::className(),
            \humhub\core\user\behaviors\UserModelModules::className()
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

    public function getUrl()
    {
        return $this->createUrl('/user/profile');
    }

    /**
     * Before Delete of a User
     *
     */
    public function beforeDelete()
    {

        // We don't allow deletion of users who owns a space - validate that
        foreach (\humhub\core\space\models\Membership::GetUserSpaces($this->id) as $space) {
            if ($space->isSpaceOwner($this->id)) {
                throw new Exception("Tried to delete a user which is owner of a space!");
            }
        }

        \humhub\core\user\models\Setting::deleteAll(['user_id' => $this->id]);

        // Disable all enabled modules
        /*
          foreach ($this->getAvailableModules() as $moduleId => $module) {
          if ($this->isModuleEnabled($moduleId)) {
          $this->disableModule($moduleId);
          }
          }
          Yii::app()->search->delete($this);


          // Delete user session
          UserHttpSession::model()->deleteAllByAttributes(array('user_id' => $this->id));

          // Delete Profile Image
          $this->getProfileImage()->delete();

          // Delete all pending invites
          UserInvite::model()->deleteAllByAttributes(array('user_originator_id' => $this->id));

          UserFollow::model()->deleteAllByAttributes(array('user_id' => $this->id));
          UserFollow::model()->deleteAllByAttributes(array('object_model' => 'User', 'object_id' => $this->id));

          // Delete all group admin assignments
          GroupAdmin::model()->deleteAllByAttributes(array('user_id' => $this->id));

          // Delete wall entries
          WallEntry::model()->deleteAllByAttributes(array('wall_id' => $this->wall_id));

          // Delete user profile
          Profile::model()->deleteAllByAttributes(array('user_id' => $this->id));

          // Deletes all content created by this user
          foreach (Content::model()->findAllByAttributes(array('user_id' => $this->id)) as $content) {
          $content->delete();
          }
          foreach (Content::model()->findAllByAttributes(array('created_by' => $this->id)) as $content) {
          $content->delete();
          }

          // Delete all passwords
          foreach (UserPassword::model()->findAllByAttributes(array('user_id' => $this->id)) as $password) {
          $password->delete();
          }
         */

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

            if (Setting::Get('allowGuestAccess', 'authentication_internal')) {
                // Set users profile default visibility to all
                if (Setting::Get('defaultUserProfileVisibility', 'authentication_internal') == User::VISIBILITY_ALL) {
                    $this->visibility = User::VISIBILITY_ALL;
                }
            }

            $this->last_activity_email = new \yii\db\Expression('NOW()');

            // Set Status
            if ($this->status == "") {
                if (Setting::Get('needApproval', 'authentication_internal')) {
                    $this->status = User::STATUS_NEED_APPROVAL;
                } else {
                    $this->status = User::STATUS_ENABLED;
                }
            }

            if ((Setting::Get('defaultUserGroup', 'authentication_internal'))) {
                $this->group_id = Setting::Get('defaultUserGroup', 'authentication_internal');
            }
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
            //Yii::app()->search->update($this);
        } else {
            //Yii::app()->search->delete($this);
        }

        if ($insert) {
            if ($this->status == User::STATUS_ENABLED)
                $this->setUpApproved();
            else
                $this->notifyGroupAdminsForApproval();

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
                $space = \humhub\core\space\models\Space::findOne(['id' => $userInvite->space_invite_id]);
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
            $space = \humhub\core\space\models\Space::findOne(['id' => $group->space_id]);
            if ($space !== null) {
                $space->addMember($this->id);
            }
        }

        // Auto Add User to the default spaces
        foreach (\humhub\core\space\models\Space::findAll(['auto_add_new_members' => 1]) as $space) {
            $space->addMember($this->id);
        }

        // Create new wall record for this user
        $wall = new \humhub\core\content\models\Wall;
        $wall->object_model = $this->className();
        $wall->object_id = $this->id;
        $wall->save();

        $this->wall_id = $wall->id;

        $this->update(false, ['wall_id']);
    }

    /**
     * Returns users display name
     *
     * @return string
     */
    public function getDisplayName()
    {

        $name = '';

        $format = Setting::Get('displayNameFormat');

        if ($this->profile !== null && $format == '{profile.firstname} {profile.lastname}')
            $name = $this->profile->firstname . " " . $this->profile->lastname;

        // Return always username as fallback
        if ($name == '' || $name == ' ')
            return $this->username;

        return $name;
    }

    /**
     * Notifies groups admins for approval of new user via e-mail.
     * This should be done after a new user is created and approval is required.
     *
     * @todo Create message template, move message into translation
     */
    private function notifyGroupAdminsForApproval()
    {
        // No admin approval required
        if ($this->status != User::STATUS_NEED_APPROVAL || !Setting::Get('needApproval', 'authentication_internal')) {
            return;
        }

        foreach (GroupAdmin::model()->findAllByAttributes(array('group_id' => $this->group_id)) as $admin) {
            $adminUser = User::model()->findByPk($admin->user_id);
            if ($adminUser !== null) {
                $approvalUrl = Yii::app()->createAbsoluteUrl("//admin/approval");

                $html = "Hello {$adminUser->displayName},<br><br>\n\n" .
                        "a new user {$this->displayName} needs approval.<br><br>\n\n" .
                        "Click here to validate:<br>\n\n" .
                        HHtml::link($approvalUrl, $approvalUrl) . "<br/> <br/>\n";

                $message = new HMailMessage();
                $message->addFrom(Setting::Get('systemEmailAddress', 'mailing'), Setting::Get('systemEmailName', 'mailing'));
                $message->addTo($adminUser->email);
                $message->view = "application.views.mail.TextOnly";
                $message->subject = Yii::t('UserModule.models_User', "New user needs approval");
                $message->setBody(array('message' => $html), 'text/html');
                Yii::app()->mail->send($message);
            } else {
                Yii::log("Could not load Group Admin User. Inconsistent Group Admin Record! User Id: " . $admin->user_id, CLogger::LEVEL_ERROR);
            }
        }

        return true;
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
        return \humhub\core\user\widgets\UserWall::widget(['user' => $this]);
    }

    /**
     * Returns an array with assigned Tags
     */
    public function getTags()
    {

        // split tags string into individual tags
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
            'firstname' => $this->profile->firstname,
            'lastname' => $this->profile->lastname,
            'title' => $this->profile->title,
        );

        if (!$this->profile->isNewRecord) {
            foreach ($this->profile->getProfileFields() as $profileField) {
                $attributes['profile_' . $profileField->internal_name] = $profileField->getUserValue($this, true);
            }
        }

        return $attributes;
    }

    /**
     * Creates an url in user scope.
     * (Adding uguid parameter to identify current user.)
     * See CController createUrl() for more details.
     *
     * @since 0.9
     * @param type $route the URL route.
     * @param type $params additional GET parameters.
     * @param type $ampersand the token separating name-value pairs in the URL.
     */
    public function createUrl($route, $params = array(), $ampersand = '&')
    {
        array_unshift($params, $route);
        if (!isset($params['uguid'])) {
            $params['uguid'] = $this->guid;
        }

        return \yii\helpers\Url::toRoute($params);
    }

    /**
     *
     * @return type
     */
    public function getSpaces()
    {

        // TODO: SHOW ONLY REAL MEMBERSHIPS
        return $this->hasMany(\humhub\core\space\models\Space::className(), ['id' => 'space_id'])
                        ->viaTable('space_membership', ['user_id' => 'id']);
    }

    /**
     * Checks if the user can create a space
     * 
     * @return boolean
     */
    public function canCreateSpace()
    {
        return ($this->canCreatePrivateSpace() || $this->canCreatePublicSpace());
    }

    /**
     * Checks if the user can create public spaces
     * 
     * @return boolean
     */
    public function canCreatePublicSpace()
    {
        if ($this->super_admin) {
            return true;
        } elseif ($this->group !== null && $this->group->can_create_public_spaces == 1) {
            return true;
        }

        return false;
    }

    /**
     * Checks if user can create private spaces
     * 
     * @return boolean
     */
    public function canCreatePrivateSpace()
    {

        if ($this->super_admin) {
            return true;
        } elseif ($this->group !== null && $this->group->can_create_private_spaces == 1) {
            return true;
        }

        return false;
    }

}
