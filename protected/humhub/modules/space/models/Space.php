<?php

namespace humhub\modules\space\models;

use Yii;
use humhub\modules\content\models\Wall;
use humhub\modules\space\models\Membership;
use humhub\modules\content\components\ContentContainerActiveRecord;

/**
 * This is the model class for table "space".
 *
 * @property integer $id
 * @property string $guid
 * @property integer $wall_id
 * @property string $name
 * @property string $description
 * @property string $website
 * @property integer $join_policy
 * @property integer $visibility
 * @property integer $status
 * @property integer $space_type_id
 * @property string $tags
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property string $ldap_dn
 * @property integer $auto_add_new_members
 */
class Space extends ContentContainerActiveRecord implements \humhub\modules\search\interfaces\Searchable
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

    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'space';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wall_id', 'join_policy', 'visibility', 'status', 'created_by', 'updated_by', 'auto_add_new_members', 'space_type_id'], 'integer'],
            [['name'], 'unique', 'targetClass' => self::className()],
            [['name'], 'required'],
            [['description', 'tags'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['join_policy'], 'in', 'range' => [0, 1, 2]],
            [['visibility'], 'in', 'range' => [0, 1, 2]],
            [['visibility'], 'checkVisibility'],
            [['guid', 'name', 'website'], 'string', 'max' => 45],
            [['website'], 'url'],
            [['ldap_dn'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['edit'] = ['name', 'description', 'website', 'tags', 'join_policy', 'visibility', 'space_type_id'];
        if (Yii::$app->user->isAdmin()) {
            $scenarios['edit'][] = 'ldap_dn';
        }

        $scenarios['create'] = ['name', 'description', 'join_policy', 'visibility', 'space_type_id'];

        return $scenarios;
    }

    /**
     * @inheritdoc
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
            'space_type_id' => Yii::t('SpaceModule.models_Space', 'Type'),
        );
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array(
            \humhub\components\behaviors\GUID::className(),
            \humhub\modules\space\behaviors\SpaceSetting::className(),
            \humhub\modules\space\behaviors\SpaceModelModules::className(),
            \humhub\modules\space\behaviors\SpaceModelMembership::className(),
            \humhub\modules\user\behaviors\Followable::className(),
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            if ($this->space_type_id == '') {
                $this->space_type_id = Type::find()->orderBy(['sort_key' => SORT_ASC])->one()->id;
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        Yii::$app->search->update($this);

        $user = \humhub\modules\user\models\User::findOne(['id' => $this->created_by]);

        if ($insert) {
            // Create new wall record for this space
            $wall = new Wall();
            $wall->object_model = $this->className();
            $wall->object_id = $this->id;
            $wall->save();
            $this->wall_id = $wall->id;
            $this->update(false, ['wall_id']);

            // Auto add creator as admin
            $membership = new Membership();
            $membership->space_id = $this->id;
            $membership->user_id = $user->id;
            $membership->status = Membership::STATUS_MEMBER;
            $membership->invite_role = 1;
            $membership->admin_role = 1;
            $membership->share_role = 1;
            $membership->save();

            $activity = new \humhub\modules\space\activities\Created;
            $activity->source = $this;
            $activity->originator = $user;
            $activity->create();
        }

        Yii::$app->cache->delete('userSpaces_' . $user->id);

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        foreach (Setting::findAll(['space_id' => $this->id]) as $spaceSetting) {
            $spaceSetting->delete();
        }

        foreach ($this->getAvailableModules() as $moduleId => $module) {
            if ($this->isModuleEnabled($moduleId)) {
                $this->disableModule($moduleId);
            }
        }

        Yii::$app->search->delete($this);

        $this->getProfileImage()->delete();

        \humhub\modules\user\models\Follow::deleteAll(['object_id' => $this->id, 'object_model' => 'Space']);

        foreach (Membership::findAll(['space_id' => $this->id]) as $spaceMembership) {
            $spaceMembership->delete();
        }

        \humhub\modules\user\models\Invite::deleteAll(['space_invite_id' => $this->id]);

        // When this workspace is used in a group as default workspace, delete the link
        foreach (\humhub\modules\user\models\Group::findAll(['space_id' => $this->id]) as $group) {
            $group->space_id = "";
            $group->save();
        }

        return parent::beforeDelete();
    }

    /**
     * Indicates that this user can join this workspace
     *
     * @param $userId User Id of User
     */
    public function canJoin($userId = "")
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        // Take current userid if none is given
        if ($userId == "")
            $userId = Yii::$app->user->id;

        // Checks if User is already member
        if ($this->isMember($userId))
            return false;

        // No one can join
        if ($this->join_policy == self::JOIN_POLICY_NONE)
            return
                    false;

        return true;
    }

    /**
     * Indicates that this user can join this workspace w
      ithout permission
     *
     * @param $userId User Id of User
     */
    public function canJoinFree($userId = "")
    {
        // Take current userid if none is given
        if ($userId == "")
            $userId = Yii::$app->user->id;

        // Checks if User is already member
        if ($this->isMember($userId))
            return false;

        // No one can join
        if ($this->join_policy == self::JOIN_POLICY_FREE)
            return true;

        return false;
    }

    /**
     * Check if current user can wri
      te to this workspace
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
            $userId = Yii::$app->user->id;

        // User needs to be member to post
        if ($this->isMember($userId))
            return true;

        return false;
    }

    /**
     * Checks if given use
      r can invite people to this workspace
     *
     * @param type $userId
     * @return type
     */
    public function canInvite($userId = "")
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        if ($userId == 0)
            $userId = Yii::$app->user->id;

        $membership = $this->getMembership($userId);

        if ($membership != null && $membership->invite_role == 1 && $membership->status == Membership::STATUS_MEMBER)
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
            $userId = Yii::$app->user->id;

        $membership = $this->getMembership($userId);

        if ($membership != null && $membership->share_role == 1 && $membership->status == Membership::STATUS_MEMBER)
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
            'title' => $this->name,
            'tags' => $this->tags,
            'type_id' => $this->space_type_id,
            'description' => $this->description,
        );
    }

    /**
     * Returns the Search Result Output
     */
    public function getSearchResult()
    {
        return Yii::$app->getController()->widget('application.modules_core.space.widgets.SpaceSearchResultWidget', array('space' => $this), true);
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
     * Creates an url in space scope.
     * (Adding sguid parameter to identify current space.)
     * See CController createUrl() for more details.
     *
     * @since 0.9
     * @param type $route the URL route.
     * @param type $params additional GET parameters.
     * @param type $ampersand the token separating name-value pairs in the URL.
     */
    public function createUrl($route = null, $params = array(), $scheme = false)
    {
        if ($route == null) {
            $route = '/space/space';
        }

        array_unshift($params, $route);
        if (!isset($params['sguid'])) {
            $params['sguid'] = $this->guid;
        }

        return \yii\helpers\Url::toRoute($params, $scheme);
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
        $user = Yii::$app->user->getIdentity();
        if (!$user->canCreatePublicSpace() && ($this->$attribute == 1 || $this->$attribute == 2)) {
            $this->addError($attribute, Yii::t('SpaceModule.models_Space', 'You cannot create public visible spaces!'));
        }

        if (!$user->canCreatePrivateSpace() && $this->$attribute == 0) {
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

    public function canAccessPrivateContent(\humhub\modules\user\models\User $user = null)
    {
        return ($this->isMember());
    }

    public function getWallOut()
    {
        return \humhub\modules\space\widgets\Wall::widget(['space' => $this]);
    }

    public function getMemberships()
    {
        $query = $this->hasMany(Membership::className(), ['space_id' => 'id']);
        $query->andWhere(['space_membership.status' => Membership::STATUS_MEMBER]);
        $query->addOrderBy(['admin_role' => SORT_DESC, 'share_role' => SORT_DESC]);
        return $query;
    }

    public function getApplicants()
    {
        $query = $this->hasMany(Membership::className(), ['space_id' => 'id']);
        $query->andWhere(['space_membership.status' => Membership::STATUS_APPLICANT]);
        return $query;
    }

    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'space_type_id']);
    }

}
