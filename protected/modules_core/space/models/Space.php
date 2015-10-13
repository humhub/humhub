<?php

/**
 * This is the model class for table "space".
 *
 * The followings are the available columns in table 'space':
 * @property integer $id
 * @property integer $wall_id
 * @property string $name
 * @property string $description
 * @property string $website
 * @property integer $join_policy
 * @property integer $visibility
 * @property integer $status
 * @property string $tags
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property integer $auto_add_new_members
 *
 * The followings are the available model relations:
 * @property Activity[] $activities
 * @property Post[] $posts
 * @property UserInvite[] $userInvites
 * @property User[] $users
 * @property Wall $wall
 * @property User $createdBy
 * @property User $updatedBy
 *
 * @author Luke
 * @package humhub.modules_core.space.models
 * @since 0.5
 */
class Space extends HActiveRecordContentContainer implements ISearchable
{

    // Join Policies
    const JOIN_POLICY_NONE = 0; // No Self Join Possible
    const JOIN_POLICY_APPLICATION = 1; // Invitation and Application Possible
    const JOIN_POLICY_FREE = 2; // Free for All
    // Visibility
    const VISIBILITY_NONE = 0; // Always invisible
    const VISIBILITY_REGISTERED_ONLY = 1; // Only for registered members
    const VISIBILITY_ALL = 2; // Visible for all (also guests)
    // Status
    const STATUS_DISABLED = 0; // Disabled
    const STATUS_ENABLED = 1; // Enabled
    const STATUS_ARCHIVED = 2; // Archived

    public $ownerUsernameSearch;

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
            'SpaceSettingBehavior' => array(
                'class' => 'application.modules_core.space.behaviors.SpaceSettingBehavior',
            ),
            'SpacesModelModulesBehavior' => array(
                'class' => 'application.modules_core.space.behaviors.SpaceModelModulesBehavior',
            ),
            'SpacesModelMembershipBehavior' => array(
                'class' => 'application.modules_core.space.behaviors.SpaceModelMembershipBehavior',
            ),
            'HFollowableBehavior' => array(
                'class' => 'application.modules_core.user.behaviors.HFollowableBehavior',
            ),
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Space the static model class
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
        return 'space';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {

        $rules = array();

        if ($this->scenario == 'edit') {
            $rules = array(
                array('name', 'required'),
                array('name', 'unique', 'caseSensitive' => false, 'className' => 'Space', 'message' => '{attribute} "{value}" is already in use! '),
                array('website', 'url'),
                array('description, tags', 'safe'),
                array('join_policy', 'in', 'range' => array(0, 1, 2)),
                array('visibility', 'checkVisibility'),
                array('visibility', 'in', 'range' => array(0, 1, 2)),
            );

            if (Yii::app()->user->isAdmin() && HSetting::Get('enabled', 'authentication_ldap')) {
                $rules[] = array('ldap_dn', 'length', 'max' => 255);
            }

            return $rules;
        }

        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name', 'required'),
            array('wall_id, join_policy, visibility, auto_add_new_members, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('name, website', 'length', 'max' => 45),
            array('ldap_dn', 'length', 'max' => 255),
            array('website', 'url'),
            array('name', 'unique', 'caseSensitive' => false, 'className' => 'Space', 'message' => '{attribute} "{value}" is already in use! '),
            array('join_policy', 'in', 'range' => array(0, 1, 2)),
            array('visibility', 'in', 'range' => array(0, 1, 2)),
            array('status', 'in', 'range' => array(0, 1, 2)),
            array('tags, description, created_at, updated_at, guid', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, wall_id, name, description, website, join_policy, visibility, tags, created_at, created_by, updated_at, updated_by, ownerUsernameSearch', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            // Active Invites
            'userInvites' => array(self::HAS_MANY, 'UserInvite', 'space_invite_id'),
            // List of space applicants
            'applicants' => array(self::HAS_MANY, 'SpaceMembership', 'space_id', 'condition' => 'status=' . SpaceMembership::STATUS_APPLICANT),
            // Approved Membership Only
            'memberships' => array(self::HAS_MANY, 'SpaceMembership', 'space_id',
                'condition' => 'memberships.status=' . SpaceMembership::STATUS_MEMBER,
                'order' => 'admin_role DESC, share_role DESC'
            ),
            // Approved Membership Only
            'membershipsLimited' => array(self::HAS_MANY, 'SpaceMembership', 'space_id',
                'condition' => 'status=' . SpaceMembership::STATUS_MEMBER,
                'order' => 'admin_role DESC, share_role DESC',
                'limit' => 50,
            ),
            'wall' => array(self::BELONGS_TO, 'Wall', 'wall_id'),
            'createdBy' => array(self::BELONGS_TO, 'User', 'created_by'),
            'updatedBy' => array(self::BELONGS_TO, 'User', 'updated_by'),
            'owner' => array(self::BELONGS_TO, 'User', 'updated_by'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'wall_id' => 'Wall',
            'name' => Yii::t('SpaceModule.models_Space', 'Name'),
            'description' => Yii::t('SpaceModule.models_Space', 'Description'),
            'website' => Yii::t('SpaceModule.models_Space', 'Website URL (optional)'),
            'join_policy' => Yii::t('SpaceModule.models_Space', 'Join Policy'),
            'ldap_dn' => Yii::t('SpaceModule.models_Space', 'Ldap DN'),
            'visibility' => Yii::t('SpaceModule.models_Space', 'Visibility'),
            'status' => Yii::t('SpaceModule.models_Space', 'Status'),
            'tags' => Yii::t('SpaceModule.models_Space', 'Tags'),
            'created_at' => Yii::t('SpaceModule.models_Space', 'Created At'),
            'created_by' => Yii::t('SpaceModule.models_Space', 'Created By'),
            'updated_at' => Yii::t('SpaceModule.models_Space', 'Updated At'),
            'updated_by' => Yii::t('SpaceModule.models_Space', 'Updated by'),
            'ownerUsernameSearch' => Yii::t('SpaceModule.models_Space', 'Owner'),
        );
    }

    /**
     * Scopes
     *
     */
    public function scopes()
    {
        return array(
            // Coming soon
            'active' => array(
                'condition' => $this->getTableAlias() . '.status=' . self::STATUS_ENABLED,
            ),
            'visible' => array(
                'condition' => $this->getTableAlias() . '.visibility != ' . Space::VISIBILITY_NONE,
            ),
            'recently' => array(
                'order' => $this->getTableAlias() . '.created_at DESC',
                'limit' => 10,
            ),
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
        $criteria->compare('wall_id', $this->wall_id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('website', $this->website, true);
        $criteria->compare('join_policy', $this->join_policy);
        $criteria->compare('t.visibility', $this->visibility);
        $criteria->compare('tags', $this->tags, true);
        $criteria->compare('created_at', $this->created_at, true);
        $criteria->compare('created_by', $this->created_by);
        $criteria->compare('updated_at', $this->updated_at, true);
        $criteria->compare('updated_by', $this->updated_by);

        $criteria->compare('owner.username', $this->ownerUsernameSearch, true);
        $criteria->join = 'JOIN user owner ON (owner.id=t.created_by)';

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * After Save Addons
     */
    protected function afterSave()
    {

        // Try To Delete Search Model
        HSearch::getInstance()->deleteModel($this);

        // Newer index a hidden workspace
        if ($this->visibility != self::VISIBILITY_NONE) {
            HSearch::getInstance()->addModel($this);
        }

        $userId = $this->created_by;

        if ($this->isNewRecord) {
            // Create new wall record for this space
            $wall = new Wall();
            $wall->object_model = 'Space';
            $wall->object_id = $this->id;
            $wall->save();
            $this->wall_id = $wall->id;
            $this->wall = $wall;
            Space::model()->updateByPk($this->id, array('wall_id' => $wall->id));

            // Auto add creator as admin
            $membership = new SpaceMembership;
            $membership->space_id = $this->id;
            $membership->user_id = $userId;
            $membership->status = SpaceMembership::STATUS_MEMBER;
            $membership->invite_role = 1;
            $membership->admin_role = 1;
            $membership->share_role = 1;
            $membership->save();

            $activity = new Activity;
            $activity->content->created_by = $userId;
            $activity->content->space_id = $this->id;
            $activity->content->user_id = $userId;
            $activity->content->visibility = Content::VISIBILITY_PUBLIC;
            $activity->created_by = $userId;
            $activity->type = "ActivitySpaceCreated";
            $activity->save();
            $activity->fire();
        }

        Yii::app()->cache->delete('userSpaces_' . $userId);

        parent::afterSave();
    }

    /**
     * Before deletion of a Space
     */
    protected function beforeDelete()
    {

        foreach (SpaceSetting::model()->findAllByAttributes(array('space_id' => $this->id)) as $spaceSetting) {
            $spaceSetting->delete();
        }

        // Disable all enabled modules
        foreach ($this->getAvailableModules() as $moduleId => $module) {
            if ($this->isModuleEnabled($moduleId)) {
                $this->disableModule($moduleId);
            }
        }

        HSearch::getInstance()->deleteModel($this);

        $this->getProfileImage()->delete();

        // Remove all Follwers
        UserFollow::model()->deleteAllByAttributes(array('object_id' => $this->id, 'object_model' => 'Space'));

        //Delete all memberships:
        //First select, then delete - done to make sure that SpaceMembership::beforeDelete() is triggered
        $spaceMemberships = SpaceMembership::model()->findAllByAttributes(array('space_id' => $this->id));
        foreach ($spaceMemberships as $spaceMembership) {
            $spaceMembership->delete();
        }

        UserInvite::model()->deleteAllByAttributes(array('space_invite_id' => $this->id));

        // Delete all content objects of this space
        foreach (Content::model()->findAllByAttributes(array('space_id' => $this->id)) as $content) {
            $content->delete();
        }

        // When this workspace is used in a group as default workspace, delete the link
        foreach (Group::model()->findAllByAttributes(array('space_id' => $this->id)) as $group) {
            $group->space_id = "";
            $group->save();
        }

        Wall::model()->deleteAllByAttributes(array('id' => $this->wall_id));

        return parent::beforeDelete();
    }

    /**
     * Indicates that this user can join this workspace
     *
     * @param $userId User Id of User
     */
    public function canJoin($userId = "")
    {
        if (Yii::app()->user->isGuest) {
            return false;
        }

        // Take current userid if none is given
        if ($userId == "")
            $userId = Yii::app()->user->id;

        // Checks if User is already member
        if ($this->isMember($userId))
            return false;

        // No one can join
        if ($this->join_policy == self::JOIN_POLICY_NONE)
            return false;

        return true;
    }

    /**
     * Indicates that this user can join this workspace without permission
     *
     * @param $userId User Id of User
     */
    public function canJoinFree($userId = "")
    {
        // Take current userid if none is given
        if ($userId == "")
            $userId = Yii::app()->user->id;

        // Checks if User is already member
        if ($this->isMember($userId))
            return false;

        // No one can join
        if ($this->join_policy == self::JOIN_POLICY_FREE)
            return true;

        return false;
    }

    /**
     * Check if current user can write to this workspace
     *
     * @param type $userId
     * @return type
     */
    public function canWrite($userId = "")
    {

        // No writes allowed for archived workspaces
        if ($this->status == Space::STATUS_ARCHIVED)
            return false;

        // Take current userid if none is given
        if ($userId == "")
            $userId = Yii::app()->user->id;

        // User needs to be member to post
        if ($this->isMember($userId))
            return true;

        return false;
    }

    /**
     * Checks if given user can invite people to this workspace
     *
     * @param type $userId
     * @return type
     */
    public function canInvite($userId = "")
    {
        if (Yii::app()->user->isGuest) {
            return false;
        }

        if ($userId == 0)
            $userId = Yii::app()->user->id;

        $membership = $this->getMembership($userId);

        if ($membership != null && $membership->invite_role == 1 && $membership->status == SpaceMembership::STATUS_MEMBER)
            return true;

        if ($this->isAdmin($userId)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if given user can share content.
     * Shared Content is public and is visible also for non members of the space.
     *
     * @param type $userId
     * @return type
     */
    public function canShare($userId = "")
    {

        if ($userId == "")
            $userId = Yii::app()->user->id;

        $membership = $this->getMembership($userId);


        if ($membership != null && $membership->share_role == 1 && $membership->status == SpaceMembership::STATUS_MEMBER)
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

        return array(
            // Assignment
            'belongsToType' => 'Space',
            'belongsToId' => $this->id,
            'belongsToGuid' => $this->guid,
            'model' => 'Space',
            'pk' => $this->id,
            'title' => $this->name,
            'url' => Yii::app()->createUrl('workspace/show', array('guid' => $this->guid)),
            // Some Indexed fields
            'tags' => $this->tags,
            'description' => $this->description,
        );
    }

    /**
     * Returns the Search Result Output
     */
    public function getSearchResult()
    {
        return Yii::app()->getController()->widget('application.modules_core.space.widgets.SpaceSearchResultWidget', array('space' => $this), true);
    }

    /**
     * Counts all Content Items related to this workspace except of Activities.
     * Additonally Comments (normally ContentAddon) will be included.
     */
    public function countItems()
    {

        $count = 0;
        $count += Content::model()->countByAttributes(array('space_id' => $this->id), 'object_model != :activityModel', array(':activityModel' => 'Activity'));
        $count += $this->getCommentCount();

        return $count;
    }

    /**
     * Counts all posts of current space
     *
     * @return Integer
     */
    public function countPosts()
    {
        /*
          $criteria = new CDbCriteria();
          $criteria->condition = "content.space_id=:space_id";
          $criteria->params = array(':space_id' => $this->id);
          return Post::model()->with('content')->count($criteria);
         */
        return Content::model()->countByAttributes(array('object_model' => 'Post', 'space_id' => $this->id));
    }

    /**
     * Counts all followers of current space
     *
     * @return Integer
     */
    public function countFollowers()
    {
        /*
          $criteria = new CDbCriteria();
          $criteria->condition = "content.space_id=:space_id";
          $criteria->params = array(':space_id' => $this->id);
          return Post::model()->with('content')->count($criteria);
         */
        return Content::model()->countByAttributes(array('object_model' => 'Post', 'space_id' => $this->id));
    }

    /**
     * Sets Comments Count for this workspace
     */
    public function getCommentCount()
    {
        $cacheId = "workspaceCommentCount_" . $this->id;
        $cacheValue = Yii::app()->cache->get($cacheId);

        if ($cacheValue === false) {
            $newCacheValue = Comment::model()->countByAttributes(array('space_id' => $this->id));
            Yii::app()->cache->set($cacheId, $newCacheValue, HSetting::Get('expireTime', 'cache'));
            return $newCacheValue;
        } else {
            return $cacheValue;
        }
    }

    /**
     * Returns an array with assigned Tags
     */
    public function getTags()
    {

        // split tags string into individual tags
        return preg_split("/[;,# ]+/", $this->tags);
    }

    /**
     * Archive this Space
     */
    public function archive()
    {
        $this->status = self::STATUS_ARCHIVED;
        $this->save();
    }

    /**
     * Unarchive this Space
     */
    public function unarchive()
    {
        $this->status = self::STATUS_ENABLED;
        $this->save();
    }

    /**
     * Returns the url to the space.
     *
     * @param array $parameters
     * @return string url
     */
    public function getUrl($parameters = array())
    {
        return $this->createUrl('//space/space', $parameters);
    }

    /**
     * Creates an url in space scope.
     * (Adding sguid parameter to identify current space.)
     * See CController createUrl() for more details.
     *
     * @since 0.9
     * @param type $route the URL route.
     * @param type $params additional GET parameters.
     * @param type $ampersand the token separating name-value pairs in the URL.
     */
    public function createUrl($route, $params = array(), $ampersand = '&')
    {
        if (!isset($params['sguid'])) {
            $params['sguid'] = $this->guid;
        }

        if (Yii::app()->getController() !== null) {
            return Yii::app()->getController()->createUrl($route, $params, $ampersand);
        } else {
            return Yii::app()->createUrl($route, $params, $ampersand);
        }
    }

    /**
     * Validator for visibility
     *
     * Used in edit scenario to check if the user really can create spaces
     * on this visibility.
     *
     * @param type $attribute
     * @param type $params
     */
    public function checkVisibility($attribute, $params)
    {
        if (!Yii::app()->user->canCreatePublicSpace() && ($this->$attribute == 1 || $this->$attribute == 2)) {
            $this->addError($attribute, Yii::t('SpaceModule.models_Space', 'You cannot create public visible spaces!'));
        }

        if (!Yii::app()->user->canCreatePrivateSpace() && $this->$attribute == 0) {
            $this->addError($attribute, Yii::t('SpaceModule.models_Space', 'You cannot create private visible spaces!'));
        }
    }

    /**
     * Returns display name (title) of space
     * 
     * @since 0.11.0
     * @return string
     */
    public function getDisplayName()
    {
        return $this->name;
    }

    public function canAccessPrivateContent(User $user = null)
    {
        return ($this->isMember());
    }

}
