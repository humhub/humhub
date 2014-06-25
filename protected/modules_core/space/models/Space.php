<?php

/**
 * This is the model class for table "workspace".
 *
 * The followings are the available columns in table 'workspace':
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
    const JOIN_POLICY_NONE = 0;  // No Self Join Possible
    const JOIN_POLICY_APPLICATION = 1; // Only Application Possible
    const JOIN_POLICY_FREE = 2;  // Free for All
    // Visibility
    const VISIBILITY_NONE = 0;  // Always invisible
    const VISIBILITY_REGISTERED_ONLY = 1; // Only for registered memebrs
    const VISIBILITY_ALL = 2;   // Free for All
    // Status
    const STATUS_DISABLED = 0;  // Disabled
    const STATUS_ENABLED = 1;   // Enabled
    const STATUS_ARCHIVED = 2;  // Archived

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
            'follower' => array(self::MANY_MANY, 'User', 'space_follow(space_id, user_id)'),
            // List of workspace applicants
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
            'name' => 'Name',
            'description' => 'Description',
            'website' => 'Website',
            'join_policy' => 'Join Policy',
            'ldap_dn' => 'Ldap DN',
            'visibility' => 'Visibility',
            'status' => 'Status',
            'tags' => 'Tags',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => Yii::t('base', 'Updated by'),
            'ownerUsernameSearch' => 'Owner',
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
                'condition' => 'status=' . self::STATUS_ENABLED,
            ),
            'visible' => array(
                'condition' => 'visibility != ' . Space::VISIBILITY_NONE,
            ),
            'recently' => array(
                'order' => 'created_at DESC',
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
        $criteria->compare('visibility', $this->visibility);
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

        parent::afterSave();
    }

    /**
     * Before deletion of a Space
     */
    protected function beforeDelete()
    {
        if (parent::beforeDelete()) {

            foreach (SpaceSetting::model()->findAllByAttributes(array('space_id' => $this->id)) as $spaceSetting) {
                $spaceSetting->delete();
            }

            HSearch::getInstance()->deleteModel($this);
            return true;
        }
    }

    /**
     * Delete a Space
     *
     */
    public function delete()
    {

        $this->getProfileImage()->delete();

        // Remove all Follwers
        SpaceFollow::model()->deleteAllByAttributes(array('space_id' => $this->id));

        // Delete all memberships
        SpaceMembership::model()->deleteAllByAttributes(array('space_id' => $this->id));

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

        $oldWallId = $this->wall_id;

        $this->wall_id = new CDbExpression('NULL');
        $this->save();

        // Delete myself
        $isOk = parent::delete();

        // Delete wall
        Wall::model()->deleteAllByAttributes(array('id' => $oldWallId));

        return $isOk;
    }

    /**
     * After Insert, create a Wall and fire some Activity Informations.
     *
     * We cannot do this in AfterSave because we need to save() the Space again.
     *
     * @return type
     */
    public function insert($attributes = null)
    {

        if (parent::insert($attributes)) {

            // Check we have a wall yet?
            $this->checkWall();

            $user = User::model()->findByPk($this->created_by);

            $activity = new Activity;
            $activity->content->space_id = $this->id;
            $activity->content->user_id = $this->created_by;
            $activity->content->visibility = Content::VISIBILITY_PUBLIC;
            $activity->type = "ActivitySpaceCreated";
            $activity->save();
            $activity->fire();

            return true;
        }
    }

    /**
     * Indicates that this user is followed by
     *
     * @param $userId User Id of User
     */
    public function isFollowedBy($userId = "")
    {
        // Take current userid if none is given
        if ($userId == "")
            $userId = Yii::app()->user->id;

        $followed = SpaceFollow::model()->findByAttributes(array('user_id' => $userId, 'space_id' => $this->id));

        if ($followed != null)
            return true;

        return false;
    }

    /**
     * Indicates that this user can join this workspace
     *
     * @param $userId User Id of User
     */
    public function canJoin($userId = "")
    {
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
     * Checks if given Userid is Member of this Space.
     *
     * @param type $userId
     * @return type
     */
    public function isMember($userId = "")
    {

        // Take current userid if none is given
        if ($userId == "")
            $userId = Yii::app()->user->id;

        $membership = $this->getMembership($userId);

        if ($membership != null && $membership->status == SpaceMembership::STATUS_MEMBER)
            return true;

        return false;
    }

    /**
     * Checks if given Userid is Admin of this Space.
     *
     * If no UserId is given, current UserId will be used
     *
     * @param type $userId
     * @return type
     */
    public function isAdmin($userId = "")
    {

        if ($userId == 0)
            $userId = Yii::app()->user->id;

        if (Yii::app()->user->isAdmin())
            return true;

        if ($this->isOwner($userId))
            return true;

        $membership = $this->getMembership($userId);

        if ($membership != null && $membership->admin_role == 1 && $membership->status == SpaceMembership::STATUS_MEMBER)
            return true;

        return false;
    }

    /**
     * Sets Owner for this workspace
     *
     * @param type $userId
     * @return type
     */
    public function setOwner($userId = "")
    {

        if ($userId == 0)
            $userId = Yii::app()->user->id;

        $this->setAdmin($userId);

        $this->created_by = $userId;
        $this->save();

        return true;
    }

    /**
     * Gets Owner for this workspace
     *
     * @return type
     */
    public function getOwner()
    {

        $user = User::model()->findByPk($this->created_by);
        return $user;
    }

    /**
     * Sets Owner for this workspace
     *
     * @param type $userId
     * @return type
     */
    public function setAdmin($userId = "")
    {

        if ($userId == 0)
            $userId = Yii::app()->user->id;

        $membership = $this->getMembership($userId);
        if ($membership != null) {
            $membership->admin_role = 1;
            $membership->save();
            return true;
        }
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

        // There is no visibility for guests, so sharing is useless anyway.
        if ($this->visibility != Space::VISIBILITY_ALL)
            return false;

        if ($userId == "")
            $userId = Yii::app()->user->id;

        $membership = $this->getMembership($userId);

        if ($membership != null && $membership->share_role == 1 && $membership->status == SpaceMembership::STATUS_MEMBER)
            return true;

        return false;
    }

    /**
     * Returns the SpaceMembership Record for this Space
     *
     * If none Record is found, null is given
     */
    public function getMembership($userId = "")
    {
        if ($userId == "")
            $userId = Yii::app()->user->id;

        $rCacheId = 'SpaceMembership_' . $userId . "_" . $this->id;
        $rCacheRes = RuntimeCache::Get($rCacheId);

        if ($rCacheRes != null)
            return $rCacheRes;

        $dbResult = SpaceMembership::model()->findByAttributes(array('user_id' => $userId, 'space_id' => $this->id));
        RuntimeCache::Set($rCacheId, $dbResult);

        return $dbResult;
    }

    /**
     * Is given User owner of this Space
     */
    public function isOwner($userId = "")
    {
        if ($userId == "")
            $userId = Yii::app()->user->id;

        if ($this->created_by == $userId) {
            return true;
        }

        return false;
    }

    /**
     * Remove Membership
     *
     * @param $userId UserId of User to Remove
     */
    public function removeMember($userId = "")
    {

        if ($userId == "")
            $userId = Yii::app()->user->id;

        $user = User::model()->findByPk($userId);
        $membership = $this->getMembership($userId);


        if ($this->isOwner($userId)) {
            return false;
        }

        if ($membership == null) {
            return true;
        }

        // If was member, create a activity for that
        if ($membership->status == SpaceMembership::STATUS_MEMBER) {
            $activity = new Activity;
            $activity->content->space_id = $this->id;
            $activity->content->user_id = $userId;
            $activity->content->visibility = Content::VISIBILITY_PRIVATE;
            $activity->type = "ActivitySpaceMemberRemoved";
            $activity->save();
            $activity->fire();
        }

        // Was invited, but declined the request
        if ($membership->status == SpaceMembership::STATUS_INVITED) {
            SpaceInviteDeclinedNotification::fire($membership->originator_user_id, $user, $this);
        }

        // Delete Membership
        SpaceMembership::model()->deleteAllByAttributes(array(
            'user_id' => $userId,
            'space_id' => $this->id,
        ));


        // Cleanup Notifications
        SpaceApprovalRequestNotification::remove($userId, $this);
        SpaceInviteNotification::remove($userId, $this);
        SpaceApprovalRequestNotification::remove($userId, $this);
    }

    /**
     * Adds an member to this space.
     *
     * This can happens after an clicking "Request Membership" Link
     * after Approval or accepting an invite.
     *
     * @param type $userId
     */
    public function addMember($userId)
    {

        $user = User::model()->findByPk($userId);

        $membership = SpaceMembership::model()->findByAttributes(array('user_id' => $userId, 'space_id' => $this->id));

        if ($membership == null) {
            // Add Membership
            $membership = new SpaceMembership;
            $membership->space_id = $this->id;
            $membership->user_id = $userId;
            $membership->status = SpaceMembership::STATUS_MEMBER;
            $membership->invite_role = 0;
            $membership->admin_role = 0;
            $membership->share_role = 0;
        } else {

            // User is already member
            if ($membership->status == SpaceMembership::STATUS_MEMBER) {
                return true;
            }

            // User requested membership
            if ($membership->status == SpaceMembership::STATUS_APPLICANT) {
                SpaceApprovalRequestAcceptedNotification::fire(Yii::app()->user->id, $user, $this);
            }

            // User was invited
            if ($membership->status == SpaceMembership::STATUS_INVITED) {
                SpaceInviteAcceptedNotification::fire($membership->originator_user_id, $user, $this);
            }

            // Update Membership
            $membership->status = SpaceMembership::STATUS_MEMBER;
        }
        $membership->save();

        // Create Wall Activity for that
        $activity = new Activity;
        $activity->content->space_id = $this->id;
        $activity->content->user_id = $userId;
        $activity->content->visibility = Content::VISIBILITY_PRIVATE;
        $activity->content->created_by = $userId;
        $activity->created_by = $userId;
        $activity->type = "ActivitySpaceMemberAdded";
        $activity->save();
        $activity->fire();

        // Cleanup Notifications
        SpaceInviteNotification::remove($userId, $this);
        SpaceApprovalRequestNotification::remove($userId, $this);
    }

    /**
     * Invites a registered user to this space
     *
     * If user is already invited, retrigger invitation.
     * If user is applicant approve it.
     *
     * @param type $userId
     * @param type $originatorUserId
     */
    public function inviteMember($userId, $originatorUserId)
    {

        $membership = $this->getMembership($userId);

        if ($membership != null) {

            // User is already member
            if ($membership->status == SpaceMembership::STATUS_MEMBER) {
                return;
            }

            // User requested already membership, just approve him
            if ($membership->status == SpaceMembership::STATUS_APPLICANT) {
                $space->addMember(Yii::app()->user->id);
                return;
            }

            // Already invite, reinvite him
            if ($membership->status == SpaceMembership::STATUS_INVITED) {
                // Remove existing notification
                SpaceInviteNotification::remove($userId, $this);
            }
        } else {
            $membership = new SpaceMembership;
        }


        $membership->space_id = $this->id;
        $membership->user_id = $userId;
        $membership->originator_user_id = $originatorUserId;

        $membership->status = SpaceMembership::STATUS_INVITED;
        $membership->invite_role = 0;
        $membership->admin_role = 0;
        $membership->share_role = 0;

        $membership->save();

        SpaceInviteNotification::fire($originatorUserId, $userId, $this);
    }

    /**
     * Invites a not registered member to this space
     *
     * @param type $email
     * @param type $originatorUserId
     */
    public function inviteMemberByEMail($email, $originatorUserId)
    {

        // Invalid E-Mail
        $validator = new CEmailValidator;
        if (!$validator->validateValue($email))
            return false;

        // User already registered
        $user = User::model()->findByAttributes(array('email' => $email));
        if ($user != null)
            return false;

        $userInvite = UserInvite::model()->findByAttributes(array('email' => $email));

        // No invite yet
        if ($userInvite == null) {
            // Invite EXTERNAL user
            $userInvite = new UserInvite();
            $userInvite->email = $email;
            $userInvite->source = UserInvite::SOURCE_INVITE;
            $userInvite->user_originator_id = $originatorUserId;
            $userInvite->space_invite_id = $this->id;
            $userInvite->save();
            $userInvite->sendInviteMail();

            // There is a pending registration
            // Steal it und send mail again
            // Unfortunately there a no multiple workspace invites supported
            // so we take the last one
        } else {
            $userInvite->user_originator_id = $originatorUserId;
            $userInvite->space_invite_id = $this->id;
            $userInvite->save();
            $userInvite->sendInviteMail();
        }
    }

    /**
     * Requests Membership
     *
     * @param type $userId
     * @param type $message
     */
    public function requestMembership($userId, $message = "")
    {

        // Add Membership
        $membership = new SpaceMembership;
        $membership->space_id = $this->id;
        $membership->user_id = $userId;
        $membership->status = SpaceMembership::STATUS_APPLICANT;
        $membership->invite_role = 0;
        $membership->admin_role = 0;
        $membership->share_role = 0;
        $membership->request_message = $message;
        $membership->save();

        SpaceApprovalRequestNotification::fire($userId, $this);
    }

    /**
     * Checks if there is already a wall created for this workspace.
     * If not, a new wall will be created and automatically assigned.
     */
    public function checkWall()
    {

        // Check if wall exists
        if ($this->wall == null) {

            // Create new Wall
            $wall = new Wall();
            $wall->type = Wall::TYPE_SPACE;
            $wall->object_model = 'Space';
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
     * Returns the Admins of this Space
     */
    public function getAdmins()
    {

        $admins = array();

        $adminMemberships = SpaceMembership::model()->findAllByAttributes(array('space_id' => $this->id, 'admin_role' => 1));

        foreach ($adminMemberships as $admin) {
            $admins[] = $admin->user;
        }

        return $admins;
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
     * Counts all posts of current workspace
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
        $parameters['sguid'] = $this->guid;
        return Yii::app()->createUrl('//space/space', $parameters);
    }

    /**
     * Collects a list of all modules which are available for this space
     *
     * @return array
     */
    public function getAvailableModules()
    {

        $modules = array();

        foreach (Yii::app()->moduleManager->getEnabledModules() as $moduleId => $module) {
            if (array_key_exists('SpaceModuleBehavior', $module->behaviors())) {
                $modules[$module->getId()] = $module;
            }
        }

        return $modules;
    }

    /**
     * Returns an array of enabled workspace modules
     *
     * @return array
     */
    public function getEnabledModules()
    {

        $modules = array();
        foreach (SpaceApplicationModule::model()->findAllByAttributes(array('space_id' => $this->id)) as $SpaceModule) {
            $moduleId = $SpaceModule->module_id;

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
    public function isModuleEnabled($moduleId)
    {

        // Not enabled globally
        if (!array_key_exists($moduleId, $this->getAvailableModules())) {
            return false;
        }

        // Not enabled at space
        $module = SpaceApplicationModule::model()->findByAttributes(array('module_id' => $moduleId, 'space_id' => $this->id));
        if ($module == null) {
            return false;
        }

        return true;
    }

    /**
     * Installs a Module
     */
    public function installModule($moduleId)
    {

        // Not enabled globally
        if (!array_key_exists($moduleId, $this->getAvailableModules())) {
            return false;
        }

        // Already enabled module
        if ($this->isModuleEnabled($moduleId)) {
            Yii::log("Space->installModule(" . $moduleId . ") module is already enabled");
            return false;
        }

        // Add Binding
        $SpaceModule = new SpaceApplicationModule();
        $SpaceModule->module_id = $moduleId;
        $SpaceModule->space_id = $this->id;
        $SpaceModule->save();

        // Fire Event
        if ($this->hasEventHandler('onInstallModule'))
            $this->onInstallModule(new CEvent($this));


        return true;
    }

    public function onInstallModule($event)
    {
        $this->raiseEvent('onInstallModule', $event);
    }

    /**
     * Uninstalls a Module
     */
    public function uninstallModule($moduleId)
    {

        // Not enabled globally
        if (!array_key_exists($moduleId, $this->getAvailableModules())) {
            return false;
        }

        // Already enabled module
        if (!$this->isModuleEnabled($moduleId)) {
            Yii::log("Space->uninstallModule(" . $moduleId . ") module is not enabled");
            return false;
        }

        // Fire Event
        if ($this->hasEventHandler('onUninstallModule'))
            $this->onUninstallModule(new CEvent($this, $moduleId));


        SpaceApplicationModule::model()->deleteAllByAttributes(array('space_id' => $this->id, 'module_id' => $moduleId));

        return true;
    }

    public function onUninstallModule($event)
    {
        $this->raiseEvent('onUninstallModule', $event);
    }

}
