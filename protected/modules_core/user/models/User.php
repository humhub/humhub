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
 * @property integer $status
 * @property string $auth_mode
 * @property string $tags
 * @property string $language
 * @property integer $receive_email_notifications
 * @property integer $receive_email_messaging
 * @property integer $receive_email_activities
 * @property string $last_activity_email
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * The followings are the available model relations:
 * @property Group $group
 * @property UserFollow[] $userFollows
 * @property UserFollow[] $userFollows1
 * @property UserInvite[] $userInvites
 * @property Message[] $messages
 * @property Space[] $workspaces
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 * @author Luke
 */
class User extends HActiveRecord implements ISearchable {

    const AUTH_MODE_LDAP = "ldap";
    const AUTH_MODE_LOCAL = "local";
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;
    const STATUS_NEED_APPROVAL = 2;
    const STATUS_DELETED = 3;
    const RECEIVE_EMAIL_NEVER = 0;
    const RECEIVE_EMAIL_DAILY_SUMMARY = 1;
    const RECEIVE_EMAIL_WHEN_OFFLINE = 2;
    const RECEIVE_EMAIL_ALWAYS = 3;

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
    public function behaviors() {
        return array(
            'HContentBaseBehavior' => array(
                'class' => 'application.behaviors.HContentBaseBehavior'
            ),
        );
    }

    public function defaultScope() {
        return array(
            // Per default show only content of users which are enabled or disabled
            'condition' => "status='" . self::STATUS_ENABLED . "' OR status='" . self::STATUS_DISABLED . "'",
        );
    }

    public function scopes() {
        return array(
            'notDeleted' => array(
                'condition' => "status != '" . self::STATUS_DELETED . "'",
            ),
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
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'user';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {

        if ($this->scenario == 'register') {
            // Only return all fields required for registration.
            // All other fields should be unsafe.
            return array(
                array('username, group_id, email', 'required'),
                array('username', 'unique', 'caseSensitive' => false, 'className' => 'User'),
                array('email', 'email'),
                array('group_id', 'numerical'),
                array('username', 'match', 'not' => true, 'pattern' => '/[^a-zA-Z0-9äöüÄÜÖß ]/', 'message' => Yii::t('UserModule.base', 'Username must consist of letters, numbers and spaces only')),
            );
        }

        $rules = array();
        $rules[] = array('wall_id, status, group_id, super_admin, created_by, updated_by', 'numerical', 'integerOnly' => true);
        $rules[] = array('email', 'email');
        $rules[] = array('guid', 'length', 'max' => 45);
        $rules[] = array('username', 'unique', 'caseSensitive' => false, 'className' => 'User');
        $rules[] = array('email', 'unique', 'caseSensitive' => false, 'className' => 'User');
        $rules[] = array('tags', 'length', 'max' => 100);
        $rules[] = array('username', 'length', 'max' => 25);
        $rules[] = array('language', 'length', 'max' => 5);
        $rules[] = array('language', 'match', 'not' => true, 'pattern' => '/[^a-zA-Z]/', 'message' => Yii::t('UserModule.base', 'Invalid language!'));
        $rules[] = array('auth_mode, tags, created_at, updated_at, last_activity_email', 'safe');
        $rules[] = array('auth_mode', 'length', 'max' => 10);
        $rules[] = array('receive_email_notifications, receive_email_messaging, receive_email_activities', 'numerical', 'integerOnly' => true);
        $rules[] = array('id, guid, status, wall_id, group_id, username, email, tags, created_at, created_by, updated_at, updated_by', 'safe', 'on' => 'search');

        return $rules;
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'activities' => array(self::HAS_MANY, 'Activity', 'user_id'),
            'portfolioItems' => array(self::MANY_MANY, 'PortfolioItem', 'portfolio_item_like(created_by, portfolio_item_id)'),
            'wall' => array(self::BELONGS_TO, 'Wall', 'wall_id'),
            'group' => array(self::BELONGS_TO, 'Group', 'group_id'),
            // Following
            'followsUser' => array(self::MANY_MANY, 'User', 'user_follow(user_follower_id,user_followed_id)'),
            'followerUser' => array(self::MANY_MANY, 'User', 'user_follow(user_followed_id, user_follower_id)'),
            'followSpaces' => array(self::MANY_MANY, 'Space', 'space_follow(user_id, space_id)'),
            // Member to be renamed
            'workspaces' => array(self::HAS_MANY, 'UserSpaceMembership', 'user_id'),
            'workspaceMemberships' => array(self::HAS_MANY, 'UserSpaceMembership', 'user_id', 'condition' => 'status=' . UserSpaceMembership::STATUS_MEMBER),
            // Users which this user has invited
            'userInvites' => array(self::HAS_MANY, 'UserInvite', 'user_originator_id'),
            'messages' => array(self::MANY_MANY, 'Message', 'user_message(user_id, message_id)'),
            'httpSessions' => array(self::HAS_MANY, 'UserHttpSession', 'user_id'),
            'currentPassword' => array(self::HAS_ONE, 'UserPassword', 'user_id', 'order' => 'id DESC')
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => Yii::t('base', 'ID'),
            'guid' => Yii::t('base', 'Guid'),
            'wall_id' => Yii::t('UserModule.base', 'Wall'),
            'group_id' => Yii::t('UserModule.base', 'Group'),
            'username' => Yii::t('UserModule.base', 'Username'),
            'email' => Yii::t('UserModule.base', 'Email'),
            'tags' => Yii::t('UserModule.base', 'Tags'),
            'language' => Yii::t('UserModule.base', 'Language'),
            'created_at' => Yii::t('base', 'Created At'),
            'created_by' => Yii::t('base', 'Created by'),
            'updated_at' => Yii::t('base', 'Updated at'),
            'updated_by' => Yii::t('base', 'Updated by'),
            'receive_email_notifications' => Yii::t('UserModule.base', 'Send notifications?'),
            'receive_email_messaging' => Yii::t('UserModule.base', 'Send messages?'),
            'receive_email_activities' => Yii::t('UserModule.base', 'Send activities?'),
        );
    }

    /**
     * Parameterized Scope for Recently 
     *
     * @param type $limit
     * @return User
     */
    public function recently($limit = 10) {
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
    public function search() {

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
    public function searchNeedApproval() {
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
    protected function beforeSave() {

        if ($this->isNewRecord) {
            if ($this->guid == "") {
                // Create GUID for new users
                $this->guid = UUID::v4();
            }
            if ($this->auth_mode == "")
                $this->auth_mode = self::AUTH_MODE_LOCAL;
        }

        return parent::beforeSave();
    }

    /**
     * After Save Addons
     *
     * @return type
     */
    protected function afterSave() {

        // Search Stuff
        if (!$this->isNewRecord) {
            HSearch::getInstance()->deleteModel($this);
        }
        if ($this->status == User::STATUS_ENABLED) {
            HSearch::getInstance()->addModel($this);
        }

        if ($this->isNewRecord) {

            // Auto Assign User to the Group Space
            $group = Group::model()->findByPk($this->group_id);
            if ($group != null && $group->space_id != "") {
                $space = Space::model()->findByPk($group->space_id);
                if ($space != null) {
                    $space->addMember($this->id);
                }
            }

            // Auto Add User to the default spaces
            foreach (Space::model()->findAllByAttributes(array('auto_add_new_members' => 1)) as $space) {
                $space->addMember($this->id);
            }
        }


        return parent::afterSave();
    }

    /**
     * Before Delete of a User
     *
     */
    public function beforeDelete() {
        if (parent::beforeDelete()) {
            HSearch::getInstance()->deleteModel($this);



            return true;
        }
    }

    /**
     * Deletes a user including all dependencies
     *
     * @return type
     */
    public function delete() {

        if (!$this->beforeDelete())
            return;

        Yii::import("application.modules.mail.models.*", true);


        // Delete Profile Image
        $this->getProfileImage()->delete();

        // Delete all pending invites
        UserInvite::model()->deleteAllByAttributes(array('user_originator_id' => $this->id));

        // Delete all follows
        UserFollow::model()->deleteAllByAttributes(array('user_follower_id' => $this->id));
        UserFollow::model()->deleteAllByAttributes(array('user_followed_id' => $this->id));

        // Delete all group admin assignments
        GroupAdmin::model()->deleteAllByAttributes(array('user_id' => $this->id));

        // Delete wall entries
        WallEntry::model()->deleteAllByAttributes(array('wall_id' => $this->wall_id));

        // Delete all content objects of this workspace (Should handled by modules)
        #foreach (Content::model()->findAllByAttributes(array('user_id' => $this->id)) as $content) {
        #    $content->delete();
        #}
        // Unbind my wall_id
        $this->save();

        // Delete Users Profile
        if (!$this->profile->isNewRecord)
            $this->profile->delete();

        // Clean up user table fields
        $this->status = User::STATUS_DELETED;
        $this->username = $this->guid;
        $this->email = "deleted_" . $this->id . "@example.com";
        $this->tags = "";
        $this->super_admin = 0;
        $this->receive_email_notifications = "";
        $this->receive_email_messaging = "";
        $this->receive_email_activities = "";
        $this->last_activity_email = "";

        // We assign a new guid, because LDAP Sync uses the GUID from LDAP Directory
        // This might cause conflicts if a LDAP User is deleted.
        $this->guid = UUID::v4();

        $this->update();

        // Delete all passwords
        foreach (UserPassword::model()->findAllByAttributes(array('user_id' => $this->id)) as $password) {
            $password->delete();
        }

        $this->afterDelete();




        return true;
    }

    /**
     * Returns URL to the User Profile
     *
     * @param array $parameters
     * @return string
     */
    public function getUrl($parameters = array()) {
        $parameters['uguid'] = $this->guid;
        return Yii::app()->createUrl('//user/profile', $parameters);
    }

    /**
     * Returns the Profile Link for this User
     *
     * @return Link
     */
    public function getProfileUrl() {
        return $this->getUrl();
    }

    /**
     * Returns an array with assigned Tags
     */
    public function getTags() {

        // split tags string into individual tags
        return preg_split("/[;,# ]+/", $this->tags);
    }

    /**
     * Indicates that this user is followed by
     *
     * @param $userId User Id of User
     */
    public function isFollowedBy($userId) {

        $followed = UserFollow::model()->findByAttributes(array('user_follower_id' => $userId, 'user_followed_id' => $this->id));

        if ($followed != null)
            return true;

        return false;
    }

    /**
     * Checks if given userId can write to this users wall
     *
     * @param type $userId
     * @return type
     */
    public function canWrite($userId = "") {

        if ($userId == "")
            $userId = Yii::app()->user->id;

        if ($userId == $this->id)
            return true;

        return false;
    }

    /**
     * Checks if a wall was created for this user
     * If not, it will created.
     */
    public function checkWall() {

        // Check if wall exists
        if ($this->wall == null) {

            // Create new Wall
            $wall = new Wall();
            $wall->type = Wall::TYPE_USER;
            $wall->object_model = 'User';
            $wall->object_id = $this->id;
            $wall->save();

            // Assign Wall
            $this->wall_id = $wall->id;
            $this->save();

            $this->wall = $wall;
        }
    }

    /**
     * Returns an array of informations used by search subsystem.
     * Function is defined in interface ISearchable
     *
     * @return Array
     */
    public function getSearchAttributes() {

        return array(
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
    }

    /**
     * Returns the Search Result Output
     */
    public function getSearchResult() {
        return Yii::app()->getController()->widget('application.modules_core.user.widgets.UserSearchResultWidget', array('user' => $this), true);
    }

    /**
     * Returns Profile Record for this user
     *
     * @return Profile
     */
    public function getProfile() {

        if ($this->_profile != null)
            return $this->_profile;

        $this->_profile = Profile::model()->findByPk($this->id);
        if ($this->_profile == null) {
            // Maybe new user?
            $this->_profile = new Profile();
            $this->_profile->user_id = $this->id;
        }

        return $this->_profile;
    }

    /**
     * Returns users display name
     *
     * @return string
     */
    public function getDisplayName() {

        $name = '';
        $format = HSetting::Get('displayNameFormat');

        if ($format == '{profile.firstname} {profile.lastname}')
            $name = $this->profile->firstname . " " . $this->profile->lastname;

        // Return always username as fallback
        if ($name == '')
            return $this->username;

        return $name;
    }

    /**
     * Registers a new user
     *
     * @param type $userInvite
     * @return boolean
     */
    public function register($userInvite) {

        $this->email = $userInvite->email;
        $this->auth_mode = User::AUTH_MODE_LOCAL;

        if (HSetting::Get('needApproval', 'authentication_internal')) {
            $this->status = User::STATUS_NEED_APPROVAL;
        } else {
            $this->status = User::STATUS_ENABLED;
        }

        if ((HSetting::Get('defaultUserGroup', 'authentication_internal'))) {
            $this->group_id = HSetting::Get('defaultUserGroup', 'authentication_internal');
        }

        $this->save();

        // User was invited to a space
        if ($userInvite->source == UserInvite::SOURCE_INVITE) {
            $space = Space::model()->findByPk($userInvite->space_invite_id);
            if ($space != null) {
                $space->addMember($this->id);
            }
        }

        // Delete/Cleanup Invite Entry
        $userInvite->delete();

        // When Approval is required, notify group administrators
        if (HSetting::Get('needApproval', 'authentication_internal')) {

            foreach (GroupAdmin::model()->findAllByAttributes(array('group_id' => $this->group_id)) as $admin) {
                $adminUser = User::model()->findByPk($admin->user_id);
                if ($adminUser != null) {

                    $approvalUrl = Yii::app()->createAbsoluteUrl("//admin/approval");
                    $html = "Hello {$adminUser->displayName},<br><br>\n\n" .
                            "a new user {$this->displayName} needs approval.<br><br>\n\n" .
                            "Click here to validate:<br>\n\n" .
                            HHtml::link($approvalUrl, $approvalUrl) . "<br/> <br/>\n";

                    $message = new HMailMessage();
                    $message->addFrom(HSetting::Get('systemEmailAddress', 'mailing'), HSetting::Get('systemEmailName', 'mailing'));
                    $message->addTo($adminUser->email);
                    $message->view = "application.views.mail.TextOnly";
                    $message->subject = Yii::t('UserModule.base', "New user needs approval");
                    $message->setBody(array('message' => $html), 'text/html');
                    Yii::app()->mail->send($message);
                }
            }
        }

        return true;
    }

    /**
     * Returns a list of available workspace modules
     *
     * @return array
     */
    public function getAvailableModules() {

        $availableModules = array();

        // Loop over all enabled modules
        foreach (Yii::app()->moduleManager->getEnabledModules() as $moduleId => $definition) {

            if (isset($definition['userModules']) && is_array($definition['userModules'])) {

                foreach ($definition['userModules'] as $moduleId => $moduleInfo) {
                    $availableModules[$moduleId] = $moduleInfo;
                }
            }
        }

        return $availableModules;
    }

    /**
     * Returns an array of enabled workspace modules
     *
     * @return array
     */
    public function getEnabledModules() {

        $modules = array();
        foreach (UserApplicationModule::model()->findAllByAttributes(array('user_id' => $this->id)) as $userModule) {
            $moduleId = $userModule->module_id;

            if (Yii::app()->moduleManager->isEnabled($moduleId)) {
                $modules[] = $moduleId;
            }
        }
        return $modules;
    }

    /**
     * Checks if given ModuleId is enabled
     *
     * @param type $moduleId
     */
    public function isModuleEnabled($moduleId) {

        // Not enabled globally
        if (!array_key_exists($moduleId, $this->getAvailableModules())) {
            return false;
        }

        // Not enabled at space
        $module = UserApplicationModule::model()->findByAttributes(array('module_id' => $moduleId, 'user_id' => $this->id));
        if ($module == null) {
            return false;
        }

        return true;
    }

    /**
     * Installs a Module
     */
    public function installModule($moduleId) {

        // Not enabled globally
        if (!array_key_exists($moduleId, $this->getAvailableModules())) {
            return false;
        }

        // Already enabled module
        if ($this->isModuleEnabled($moduleId)) {
            Yii::log("User->installModule(" . $moduleId . ") module is already enabled");
            return false;
        }

        // Add Binding
        $userModule = new UserApplicationModule();
        $userModule->module_id = $moduleId;
        $userModule->user_id = $this->id;
        $userModule->save();

        // Fire Event
        if ($this->hasEventHandler('onInstallModule'))
            $this->onInstallModule(new CEvent($this));


        return true;
    }

    public function onInstallModule($event) {
        $this->raiseEvent('onInstallModule', $event);
    }

    /**
     * Uninstalls a Module
     */
    public function uninstallModule($moduleId) {

        // Not enabled globally
        if (!array_key_exists($moduleId, $this->getAvailableModules())) {
            return false;
        }

        // Already enabled module
        if (!$this->isModuleEnabled($moduleId)) {
            Yii::log("User->uninstallModule(" . $moduleId . ") module is not enabled");
            return false;
        }

        // Fire Event
        if ($this->hasEventHandler('onUninstallModule'))
            $this->onUninstallModule(new CEvent($this, $moduleId));


        UserApplicationModule::model()->deleteAllByAttributes(array('user_id' => $this->id, 'module_id' => $moduleId));

        return true;
    }

    public function onUninstallModule($event) {
        $this->raiseEvent('onUninstallModule', $event);
    }

    /**
     * ------------------------------------------------------------------------------------------------
     * Temporary
     * ------------------------------------------------------------------------------------------------
     */
    public function getFirstname() {
        return $this->profile->firstname;
    }

    public function getLastname() {
        return $this->profile->lastname;
    }

    public function getTitle() {
        return $this->profile->title;
    }

}
