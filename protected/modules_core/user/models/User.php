<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $id
 * @property string $guid
 * @property integer $wall_id
 * @property integer $group_id
 * @property string $username
 * @property string $email
 * @property integer $super_admin
 * @property integer $visiblity
 * @property integer $status
 * @property string $auth_mode
 * @property string $tags
 * @property string $language
 * @property string $last_activity_email
 * @property string $last_login
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * The followings are the available model relations:
 * @property Group $group
 * @property UserInvite[] $userInvites
 * @property Message[] $messages
 * @property Space[] $workspaces
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 * @author Luke
 */
class User extends HActiveRecordContentContainer implements ISearchable
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
     * Loaded User Profile
     *
     * @var type
     */

    protected $_profile;

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors()
    {
        return array(
            'HGuidBehavior' => array(
                'class' => 'application.behaviors.HGuidBehavior',
            ),
            'UserSettingBehavior' => array(
                'class' => 'application.modules_core.user.behaviors.UserSettingBehavior',
            ),
            'HFollowableBehavior' => array(
                'class' => 'application.modules_core.user.behaviors.HFollowableBehavior',
            ),
            'UserModelModulesBehavior' => array(
                'class' => 'application.modules_core.user.behaviors.UserModelModulesBehavior',
            )
        );
    }

    public function defaultScope()
    {
        return array(
            // Per default show only content of users which are enabled or disabled
            'condition' => "status='" . self::STATUS_ENABLED . "' OR status='" . self::STATUS_DISABLED . "'",
        );
    }

    public function scopes()
    {
        return array(
            'unapproved' => array(
                'condition' => "status = '" . self::STATUS_NEED_APPROVAL . "'",
            ),
            'active' => array(
                'condition' => 'status = ' . self::STATUS_ENABLED,
            ),
            'recently' => array(
                'order' => 'created_at DESC',
                'limit' => 10,
            ),
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return User the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'user';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {

        if ($this->scenario == 'register') {
            // Only return all fields required for registration.
            // All other fields should be unsafe.
            return array(
                array('username, group_id, email', 'required'),
                array('username', 'unique', 'caseSensitive' => false, 'className' => 'User'),
                array('email', 'email'),
                array('group_id', 'numerical'),
                array('email', 'unique', 'caseSensitive' => false, 'className' => 'User'),
                array('username', 'match', 'not' => true, 'pattern' => '/[^a-zA-Z0-9äöüÄÜÖß\+\-\._ ]/', 'message' => Yii::t('UserModule.models_User', 'Username can contain only letters, numbers, spaces and special characters (+-._)')),
                array('username', 'length', 'max' => 25, 'min' => 4),
            );
        }

        $rules = array();
        $rules[] = array('wall_id, status, group_id, super_admin, created_by, updated_by, visibility', 'numerical', 'integerOnly' => true);
        $rules[] = array('email', 'email');
        $rules[] = array('guid', 'length', 'max' => 45);
        $rules[] = array('username', 'unique', 'caseSensitive' => false, 'className' => 'User');
        $rules[] = array('email', 'unique', 'caseSensitive' => false, 'className' => 'User');
        $rules[] = array('email,tags', 'length', 'max' => 100);
        $rules[] = array('username', 'length', 'max' => 25, 'min' => 4);
        $rules[] = array('language', 'length', 'max' => 5);
        $rules[] = array('language', 'match', 'not' => true, 'pattern' => '/[^a-zA-Z_]/', 'message' => Yii::t('UserModule.models_User', 'Invalid language!'));
        $rules[] = array('auth_mode, tags, created_at, updated_at, last_activity_email, last_login', 'safe');
        $rules[] = array('auth_mode', 'length', 'max' => 10);
        $rules[] = array('id, guid, status, wall_id, group_id, username, email, tags, created_at, created_by, updated_at, updated_by', 'safe', 'on' => 'search');

        return $rules;
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'wall' => array(self::BELONGS_TO, 'Wall', 'wall_id'),
            'group' => array(self::BELONGS_TO, 'Group', 'group_id'),
            'spaces' => array(self::HAS_MANY, 'SpaceMembership', 'user_id'),
            'spaceMemberships' => array(self::HAS_MANY, 'SpaceMembership', 'user_id', 'condition' => 'status=' . SpaceMembership::STATUS_MEMBER),
            'userInvites' => array(self::HAS_MANY, 'UserInvite', 'user_originator_id'),
            'httpSessions' => array(self::HAS_MANY, 'UserHttpSession', 'user_id'),
            'currentPassword' => array(self::HAS_ONE, 'UserPassword', 'user_id', 'order' => 'id DESC'),
            'userProfile' => array(self::HAS_ONE, 'Profile', 'user_id')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Yii::t('UserModule.models_User', 'ID'),
            'guid' => Yii::t('UserModule.models_User', 'Guid'),
            'wall_id' => Yii::t('UserModule.models_User', 'Wall'),
            'group_id' => Yii::t('UserModule.models_User', 'Group'),
            'username' => Yii::t('UserModule.models_User', 'Username'),
            'visibility' => Yii::t('UserModule.models_User', 'Visibility'),
            'email' => Yii::t('UserModule.models_User', 'Email'),
            'tags' => Yii::t('UserModule.models_User', 'Tags'),
            'auth_mode' => Yii::t('UserModule.models_User', 'Authentication mode'),
            'language' => Yii::t('UserModule.models_User', 'Language'),
            'created_at' => Yii::t('UserModule.models_User', 'Created At'),
            'created_by' => Yii::t('UserModule.models_User', 'Created by'),
            'updated_at' => Yii::t('UserModule.models_User', 'Updated at'),
            'updated_by' => Yii::t('UserModule.models_User', 'Updated by'),
        );
    }

    /**
     * Parameterized Scope for Recently
     *
     * @param type $limit
     * @return User
     */
    public function recently($limit = 10)
    {
        $this->getDbCriteria()->mergeWith(array(
            'order' => 'created_at DESC',
            'limit' => $limit,
        ));
        return $this;
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {

        $criteria = new CDbCriteria;
        $criteria->compare('id', $this->id);
        $criteria->compare('guid', $this->guid, true);
        $criteria->compare('group_id', $this->group_id);
        $criteria->compare('username', $this->username, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('super_admin', $this->super_admin);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('created_by', $this->created_by);
        $criteria->compare('updated_at', $this->updated_at, true);
        $criteria->compare('updated_by', $this->updated_by);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
            // For Admin User Search
            'pagination' => array(
                'pageSize' => 25,
            ),
        ));
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function searchNeedApproval()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('guid', $this->guid, true);
        $criteria->compare('wall_id', $this->wall_id);

        if (Yii::app()->user->isAdmin()) {
            $criteria->compare('group_id', $this->group_id);
        } else {
            $groups = array();
            $adminGroups = GroupAdmin::model()->findAllByAttributes(array('user_id' => Yii::app()->user->id));
            foreach ($adminGroups as $g) {
                $groups[] = $g->group_id;
            }

            if ($this->group_id != "" && in_array($this->group_id, $groups)) {
                $criteria->compare('group_id', $this->group_id);
            } else {
                $criteria->compare('group_id', $groups);
            }
        }

        $criteria->compare('username', $this->username, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('status', 2);
        $criteria->compare('super_admin', $this->super_admin);
        $criteria->compare('tags', $this->tags, true);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('created_by', $this->created_by);
        $criteria->compare('updated_at', $this->updated_at, true);
        $criteria->compare('updated_by', $this->updated_by);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Before Save Addons
     *
     * @return type
     */
    protected function beforeSave()
    {

        if ($this->isNewRecord) {

            if ($this->auth_mode == "") {
                $this->auth_mode = self::AUTH_MODE_LOCAL;
            }

            if (HSetting::Get('allowGuestAccess', 'authentication_internal')) {
                // Set users profile default visibility to all
                if (HSetting::Get('defaultUserProfileVisibility', 'authentication_internal') == User::VISIBILITY_ALL) {
                    $this->visibility = User::VISIBILITY_ALL;
                }
            }

            $this->last_activity_email = new CDbExpression('NOW()');

            // Set Status
            if ($this->status == "") {
                if (HSetting::Get('needApproval', 'authentication_internal')) {
                    $this->status = User::STATUS_NEED_APPROVAL;
                } else {
                    $this->status = User::STATUS_ENABLED;
                }
            }

            if ((HSetting::Get('defaultUserGroup', 'authentication_internal'))) {
                $this->group_id = HSetting::Get('defaultUserGroup', 'authentication_internal');
            }
        }

        return parent::beforeSave();
    }

    /**
     * After Save Addons
     *
     * @return type
     */
    protected function afterSave()
    {

        // Search Stuff
        if (!$this->isNewRecord) {
            HSearch::getInstance()->deleteModel($this);
        }
        if ($this->status == User::STATUS_ENABLED) {
            HSearch::getInstance()->addModel($this);
        }

        if ($this->isNewRecord) {
            if ($this->status == User::STATUS_ENABLED)
                $this->setUpApproved();
            else
                $this->notifyGroupAdminsForApproval();
        }


        return parent::afterSave();
    }

    public function setUpApproved()
    {

        $userInvite = UserInvite::model()->findByAttributes(array('email' => $this->email));
        if ($userInvite !== null) {
            // User was invited to a space
            if ($userInvite->source == UserInvite::SOURCE_INVITE) {
                $space = Space::model()->findByPk($userInvite->space_invite_id);
                if ($space != null) {
                    $space->addMember($this->id);
                }
            }

            // Delete/Cleanup Invite Entry
            $userInvite->delete();
        }

        // Auto Assign User to the Group Space
        $group = Group::model()->findByPk($this->group_id);
        if ($group != null && $group->space_id != "") {
            $space = Space::model()->findByPk($group->space_id);
            if ($space !== null) {
                $space->addMember($this->id);
            }
        }

        //
        // Auto Add User to the default spaces
        foreach (Space::model()->findAllByAttributes(array('auto_add_new_members' => 1)) as $space) {
            $space->addMember($this->id);
        }

        // Create new wall record for this user
        $wall = new Wall();
        $wall->object_model = 'User';
        $wall->object_id = $this->id;
        $wall->save();

        $this->wall_id = $wall->id;
        $this->wall = $wall;
        User::model()->updateByPk($this->id, array('wall_id' => $wall->id));
    }

    /**
     * Before Delete of a User
     *
     */
    public function beforeDelete()
    {

        // We don't allow deletion of users who owns a space - validate that
        foreach (SpaceMembership::GetUserSpaces($this->id) as $workspace) {
            if ($workspace->isSpaceOwner($this->id)) {
                throw new Exception("Tried to delete a user which is owner of a space!");
            }
        }

        UserSetting::model()->deleteAllByAttributes(array('user_id' => $this->id));

        // Disable all enabled modules
        foreach ($this->getAvailableModules() as $moduleId => $module) {
            if ($this->isModuleEnabled($moduleId)) {
                $this->disableModule($moduleId);
            }
        }

        HSearch::getInstance()->deleteModel($this);

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

        return parent::beforeDelete();
    }

    /**
     * Returns URL to the User Profile
     *
     * @param array $parameters
     * @return string
     */
    public function getUrl($parameters = array())
    {
        return $this->createUrl('//user/profile', $parameters);
    }

    /**
     * Returns the Profile Link for this User
     *
     * @deprecated since version 0.8
     * @return Link
     */
    public function getProfileUrl()
    {
        return $this->getUrl();
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
        if (!isset($params['uguid'])) {
            $params['uguid'] = $this->guid;
        }

        if (Yii::app()->getController() !== null) {
            return Yii::app()->getController()->createUrl($route, $params, $ampersand);
        } else {
            return Yii::app()->createUrl($route, $params, $ampersand);
        }
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
            $userId = Yii::app()->user->id;

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
            // Assignment
            'belongsToType' => 'User',
            'belongsToId' => $this->id,
            'belongsToGuid' => $this->guid,
            'model' => 'User',
            'pk' => $this->id,
            'title' => $this->getDisplayName(),
            'url' => $this->getUrl(),
            'tags' => $this->tags,
            'email' => $this->email,
            'groupId' => $this->group_id,
            'status' => $this->status,
            'username' => $this->username,
        );

        $profile = $this->getProfile();

        if (!$profile->isNewRecord) {
            foreach ($profile->getProfileFields() as $profileField) {
                $attributes['profile_' . $profileField->internal_name] = $profileField->getUserValue($this, true);
            }
        }

        return $attributes;
    }

    /**
     * Returns the Search Result Output
     */
    public function getSearchResult()
    {
        return Yii::app()->getController()->widget('application.modules_core.user.widgets.UserSearchResultWidget', array('user' => $this), true);
    }

    /**
     * Returns Profile Record for this user
     *
     * @return Profile
     */
    public function getProfile()
    {

        if ($this->_profile != null)
            return $this->_profile;

        $this->_profile = Profile::model()->findByPk($this->id);

        if ($this->_profile == null) {
            // Maybe new user?
            $this->_profile = new Profile();
            $this->_profile->user_id = $this->id;
        }
        $this->_profile->user = $this;

        return $this->_profile;
    }

    /**
     * Returns users display name
     *
     * @return string
     */
    public function getDisplayName()
    {

        $name = '';

        $format = HSetting::Get('displayNameFormat');

        if ($format == '{profile.firstname} {profile.lastname}')
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
        if ($this->status != User::STATUS_NEED_APPROVAL || !HSetting::Get('needApproval', 'authentication_internal')) {
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
                $message->addFrom(HSetting::Get('systemEmailAddress', 'mailing'), HSetting::Get('systemEmailName', 'mailing'));
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
        if (Yii::app()->user->id == $this->id) {
            return true;
        }

        return false;
    }

    public function canAccessPrivateContent(User $user = null)
    {
        return ($this->isCurrentUser());
    }

}
