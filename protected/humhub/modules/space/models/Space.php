<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\models;

use humhub\libs\ProfileImage;
use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\search\interfaces\Searchable;
use humhub\modules\search\events\SearchAddEvent;
use humhub\modules\search\jobs\DeleteDocument;
use humhub\modules\search\jobs\UpdateDocument;
use humhub\modules\space\behaviors\SpaceModelMembership;
use humhub\modules\space\behaviors\SpaceController;
use humhub\modules\space\components\ActiveQuerySpace;
use humhub\modules\space\Module;
use humhub\modules\user\behaviors\Followable;
use humhub\components\behaviors\GUID;
use humhub\modules\content\components\behaviors\SettingsBehavior;
use humhub\modules\content\components\behaviors\CompatModuleManager;
use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\space\components\UrlValidator;
use humhub\modules\space\activities\Created;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\GroupSpace;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\Invite;
use humhub\modules\space\widgets\Wall;
use humhub\modules\user\models\User as UserModel;
use Yii;

/**
 * This is the model class for table "space".
 *
 * @property integer $id
 * @property string $guid
 * @property string $name
 * @property string $description
 * @property string $about
 * @property string $url
 * @property integer $join_policy
 * @property integer $visibility
 * @property integer $status
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 * @property integer $auto_add_new_members
 * @property integer $contentcontainer_id
 * @property integer $default_content_visibility
 * @property string $color
 * @property User $ownerUser the owner of this space
 *
 * @mixin \humhub\components\behaviors\GUID
 * @mixin \humhub\modules\content\components\behaviors\SettingsBehavior
 * @mixin \humhub\modules\space\behaviors\SpaceModelMembership
 * @mixin \humhub\modules\user\behaviors\Followable
 * @mixin \humhub\modules\content\components\behaviors\CompatModuleManager
 */
class Space extends ContentContainerActiveRecord implements Searchable
{

    // Join Policies
    const JOIN_POLICY_NONE = 0; // No Self Join Possible
    const JOIN_POLICY_APPLICATION = 1; // Invitation and Application Possible
    const JOIN_POLICY_FREE = 2; // Free for All
    // Visibility: Who can view the space content.
    const VISIBILITY_NONE = 0; // Private: This space is invisible for non-space-members
    const VISIBILITY_REGISTERED_ONLY = 1; // Only registered users (no guests)
    const VISIBILITY_ALL = 2; // Public: All Users (Members and Guests)
    // Status
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;
    const STATUS_ARCHIVED = 2;
    // UserGroups
    const USERGROUP_OWNER = 'owner';
    const USERGROUP_ADMIN = 'admin';
    const USERGROUP_MODERATOR = 'moderator';
    const USERGROUP_MEMBER = 'member';
    const USERGROUP_USER = 'user';
    const USERGROUP_GUEST = 'guest';
    // Model Scenarios
    const SCENARIO_CREATE = 'create';
    const SCENARIO_EDIT = 'edit';
    const SCENARIO_SECURITY_SETTINGS = 'security_settings';

    /**
     * @inheritdoc
     */
    public $controllerBehavior = SpaceController::class;

    /**
     * @inheritdoc
     */
    public $defaultRoute = '/space/space';

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
        $rules = [
            [['join_policy', 'visibility', 'status', 'auto_add_new_members', 'default_content_visibility'], 'integer'],
            [['name'], 'required'],
            [['description', 'about', 'color'], 'string'],
            [['tagsField', 'blockedUsersField'], 'safe'],
            [['description'], 'string', 'max' => 100],
            [['join_policy'], 'in', 'range' => [0, 1, 2]],
            [['visibility'], 'in', 'range' => [0, 1, 2]],
            [['visibility'], 'checkVisibility'],
            [['url'], 'unique', 'skipOnEmpty' => 'true'],
            [['guid', 'name'], 'string', 'max' => 45, 'min' => 2],
            [['url'], 'string', 'max' => Yii::$app->getModule('space')->maximumSpaceUrlLength, 'min' => Yii::$app->getModule('space')->minimumSpaceUrlLength],
            [['url'], UrlValidator::class],
        ];

        if (Yii::$app->getModule('space')->useUniqueSpaceNames) {
            $rules[] = [['name'], 'unique', 'targetClass' => static::class, 'when' => function ($model) {
                return $model->isAttributeChanged('name');
            }];
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[static::SCENARIO_EDIT] = ['name', 'color', 'description', 'about', 'tagsField', 'blockedUsersField', 'join_policy', 'visibility', 'default_content_visibility', 'url'];
        $scenarios[static::SCENARIO_CREATE] = ['name', 'color', 'description', 'join_policy', 'visibility'];
        $scenarios[static::SCENARIO_SECURITY_SETTINGS] = ['default_content_visibility', 'join_policy', 'visibility'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::t('SpaceModule.base', 'Name'),
            'color' => Yii::t('SpaceModule.base', 'Color'),
            'description' => Yii::t('SpaceModule.base', 'Description'),
            'about' => Yii::t('SpaceModule.base', 'About'),
            'join_policy' => Yii::t('SpaceModule.base', 'Join Policy'),
            'visibility' => Yii::t('SpaceModule.base', 'Visibility'),
            'status' => Yii::t('SpaceModule.base', 'Status'),
            'tagsField' => Yii::t('SpaceModule.base', 'Tags'),
            'created_at' => Yii::t('SpaceModule.base', 'Created At'),
            'created_by' => Yii::t('SpaceModule.base', 'Created By'),
            'updated_at' => Yii::t('SpaceModule.base', 'Updated At'),
            'updated_by' => Yii::t('SpaceModule.base', 'Updated by'),
            'ownerUsernameSearch' => Yii::t('SpaceModule.base', 'Owner'),
            'default_content_visibility' => Yii::t('SpaceModule.base', 'Default content visibility'),
            'blockedUsersField' => Yii::t('SpaceModule.base', 'Blocked users'),
        ];
    }


    public function attributeHints()
    {
        return [
            'visibility' => Yii::t('SpaceModule.manage', 'Choose the security level for this workspace to define the visibleness.'),
            'join_policy' => Yii::t('SpaceModule.manage', 'Choose the kind of membership you want to provide for this workspace.'),
            'default_content_visibility' => Yii::t('SpaceModule.manage', 'Choose if new content should be public or private by default'),
            'description' => Yii::t('SpaceModule.base', 'Max. 100 characters.'),
            'about' => Yii::t('SpaceModule.base', 'Shown on About Page.'),
        ];
    }

    /**
     * @return array
     * @since 1.7
     */
    public static function visibilityOptions()
    {
        return [
            self::VISIBILITY_NONE => Yii::t('SpaceModule.base', 'Private (Invisible)'),
            self::VISIBILITY_REGISTERED_ONLY => Yii::t('SpaceModule.base', 'Public (Registered users only)'),
            self::VISIBILITY_ALL => Yii::t('SpaceModule.base', 'Visible for all (members and guests)'),
        ];
    }

    /**
     * @return array
     * @since 1.7
     */
    public static function joinPolicyOptions()
    {
        return [
            self::JOIN_POLICY_NONE => Yii::t('SpaceModule.base', 'Only by invite'),
            self::JOIN_POLICY_APPLICATION => Yii::t('SpaceModule.base', 'Invite and request'),
            self::JOIN_POLICY_FREE => Yii::t('SpaceModule.base', 'Everyone can enter'),
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
            SpaceModelMembership::class,
            Followable::class,
            CompatModuleManager::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        Yii::$app->queue->push(new UpdateDocument([
            'activeRecordClass' => get_class($this),
            'primaryKey' => $this->id
        ]));

        $user = User::findOne(['id' => $this->created_by]);

        if ($insert) {
            // Auto add creator as admin
            $membership = new Membership();
            $membership->space_id = $this->id;
            $membership->user_id = $user->id;
            $membership->status = Membership::STATUS_MEMBER;
            $membership->group_id = self::USERGROUP_ADMIN;
            $membership->save();

            $activity = new Created;
            $activity->source = $this;
            $activity->originator = $user;
            $activity->create();
        }

        Yii::$app->cache->delete('userSpaces_' . $user->id);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->url = UrlValidator::autogenerateUniqueSpaceUrl($this->name);
        }

        if ($this->url == '') {
            $this->url = new \yii\db\Expression('NULL');
        } else {
            $this->url = mb_strtolower($this->url);
        }

        // Make sure visibility attribute is not empty
        if (empty($this->visibility)) {
            $this->visibility = self::VISIBILITY_NONE;
        }

        if ($this->visibility == self::VISIBILITY_NONE) {
            $this->join_policy = self::JOIN_POLICY_NONE;
            $this->default_content_visibility = Content::VISIBILITY_PRIVATE;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        foreach ($this->moduleManager->getEnabled() as $module) {
            $this->moduleManager->disable($module);
        }

        Yii::$app->queue->push(new DeleteDocument([
            'activeRecordClass' => get_class($this),
            'primaryKey' => $this->id
        ]));


        $this->getProfileImage()->delete();
        $this->getProfileBannerImage()->delete();

        Follow::deleteAll(['object_id' => $this->id, 'object_model' => 'Space']);

        foreach (Membership::findAll(['space_id' => $this->id]) as $spaceMembership) {
            $spaceMembership->delete();
        }

        Invite::deleteAll(['space_invite_id' => $this->id]);
        GroupSpace::deleteAll(['space_id' => $this->id]);

        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     * @return ActiveQuerySpace
     */
    public static function find()
    {
        return new ActiveQuerySpace(get_called_class());
    }


    /**
     * Indicates that this user can join this workspace
     *
     * @param $userId User Id of User
     */
    public function canJoin($userId = '')
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        // Take current userId if none is given
        if ($userId == '') {
            $userId = Yii::$app->user->id;
        }

        // Checks if User is already member
        if ($this->isMember($userId)) {
            return false;
        }

        if ($this->join_policy == self::JOIN_POLICY_NONE) {
            return false;
        }

        if ($this->isBlockedForUser(User::findOne($userId))) {
            return false;
        }

        return true;
    }

    /**
     * Indicates that this user can join this workspace w
     * ithout permission
     *
     * @param $userId User Id of User
     */
    public function canJoinFree($userId = '')
    {
        // Take current userid if none is given
        if ($userId == '') {
            $userId = Yii::$app->user->id;
        }

        // Checks if User is already member
        if ($this->isMember($userId)) {
            return false;
        }

        if ($this->join_policy == self::JOIN_POLICY_FREE) {
            return true;
        }

        return false;
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
            'title' => $this->name,
            'tags' => implode(', ', $this->getTags()),
            'description' => $this->description
        ];

        $this->trigger(self::EVENT_SEARCH_ADD, new SearchAddEvent($attributes));

        return $attributes;
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
     * Returns wether or not a Space is archived.
     *
     * @return boolean
     * @since 1.2
     */
    public function isArchived()
    {
        return $this->status === self::STATUS_ARCHIVED;
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
        $visibility = $this->$attribute;

        // Not changed
        if (!$this->isNewRecord && $visibility == $this->getOldAttribute($attribute)) {
            return;
        }

        if ($visibility == self::VISIBILITY_NONE && !Yii::$app->user->permissionManager->can(new CreatePrivateSpace())) {
            $this->addError($attribute, Yii::t('SpaceModule.base', 'You cannot create private visible spaces!'));
        }

        if (($visibility == self::VISIBILITY_REGISTERED_ONLY || $visibility == self::VISIBILITY_ALL) && !Yii::$app->user->permissionManager->can(new CreatePublicSpace())) {
            $this->addError($attribute, Yii::t('SpaceModule.base', 'You cannot create public visible spaces!'));
        }
    }

    /**
     * @inheritdoc
     */
    public function getDisplayName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getDisplayNameSub()
    {
        return $this->description;
    }

    /**
     * @inheritDoc
     */
    public function getProfileImage()
    {
        return new $this->profileImageClass($this, 'default_space');
    }

    /**
     * @inheritdoc
     */
    public function canAccessPrivateContent(User $user = null)
    {
        $user = !$user && !Yii::$app->user->isGuest ? Yii::$app->user->getIdentity() : $user;

        if (!$user) {
            return false;
        }

        if (Yii::$app->getModule('space')->globalAdminCanAccessPrivateContent && $user->isSystemAdmin()) {
            return true;
        }

        return ($this->isMember($user));
    }

    /**
     * @inheritdoc
     */
    public function getWallOut()
    {
        return Wall::widget(['space' => $this]);
    }

    /**
     * Returns all Membership relations with status = STATUS_MEMBER.
     *
     * Be aware that this function will also include disabled users, in order to only include active and visible users use:
     *
     * ```
     * Membership::getSpaceMembersQuery($this->space)->active()->visible()->count()
     * ```
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMemberships()
    {
        $query = $this->hasMany(Membership::class, ['space_id' => 'id']);
        $query->andWhere(['space_membership.status' => Membership::STATUS_MEMBER]);
        $query->addOrderBy(['space_membership.group_id' => SORT_DESC]);

        return $query;
    }

    public function getMembershipUser($status = null)
    {
        $status = ($status == null) ? Membership::STATUS_MEMBER : $status;
        $query = User::find();
        $query->leftJoin('space_membership', 'space_membership.user_id=user.id AND space_membership.space_id=:space_id AND space_membership.status=:member', ['space_id' => $this->id, 'member' => $status]);
        $query->andWhere('space_membership.space_id IS NOT NULL');
        $query->addOrderBy(['space_membership.group_id' => SORT_DESC]);

        return $query;
    }

    public function getNonMembershipUser()
    {
        $query = User::find();
        $query->leftJoin('space_membership', 'space_membership.user_id=user.id AND space_membership.space_id=:space_id ', ['space_id' => $this->id]);
        $query->andWhere('space_membership.space_id IS NULL');
        $query->orWhere(['!=', 'space_membership.status', Membership::STATUS_MEMBER]);
        $query->addOrderBy(['space_membership.group_id' => SORT_DESC]);

        return $query;
    }

    public function getApplicants()
    {
        $query = $this->hasMany(Membership::class, ['space_id' => 'id']);
        $query->andWhere(['space_membership.status' => Membership::STATUS_APPLICANT]);

        return $query;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwnerUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Return user groups
     *
     * @return array user groups
     */
    public static function getUserGroups()
    {
        $groups = [
            self::USERGROUP_OWNER => Yii::t('SpaceModule.base', 'Owner'),
            self::USERGROUP_ADMIN => Yii::t('SpaceModule.base', 'Administrators'),
            self::USERGROUP_MODERATOR => Yii::t('SpaceModule.base', 'Moderators'),
            self::USERGROUP_MEMBER => Yii::t('SpaceModule.base', 'Members'),
            self::USERGROUP_USER => Yii::t('SpaceModule.base', 'Users')
        ];

        // Add guest groups if enabled
        if (AuthHelper::isGuestAccessEnabled()) {
            $groups[self::USERGROUP_GUEST] = Yii::t('SpaceModule.base', 'Guests');
        }

        return $groups;
    }

    /**
     * @inheritdoc
     */
    public function getUserGroup(User $user = null)
    {
        $user = !$user && !Yii::$app->user->isGuest ? Yii::$app->user->getIdentity() : $user;

        if (!$user) {
            return self::USERGROUP_GUEST;
        }

        /* @var  $membership  Membership */
        $membership = $this->getMembership($user);

        if ($membership && $membership->isMember()) {
            if ($this->isSpaceOwner($user->id)) {
                return self::USERGROUP_OWNER;
            }
            return $membership->group_id;
        } else {
            return self::USERGROUP_USER;
        }
    }

    /**
     * Returns the default content visibility
     *
     * @return int the default visiblity
     * @see Content
     */
    public function getDefaultContentVisibility()
    {
        if ($this->default_content_visibility === null) {
            $globalDefault = Yii::$app->getModule('space')->settings->get('defaultContentVisibility');
            if ($globalDefault == Content::VISIBILITY_PUBLIC) {
                return Content::VISIBILITY_PUBLIC;
            }
        } elseif ($this->default_content_visibility === 1) {
            return Content::VISIBILITY_PUBLIC;
        }

        return Content::VISIBILITY_PRIVATE;
    }

    /**
     * @inheritdoc
     */
    public function getSettings(): ContentContainerSettingsManager
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('space');
        return $module->settings->contentContainer($this);
    }

    /**
     * Returns space privileged groups and their members` User model in array
     *
     * @return array
     * @since 1.7
     */
    public function getPrivilegedGroupUsers()
    {
        $owner = $this->getOwnerUser()->one();
        $groups[self::USERGROUP_OWNER][] = $owner;

        $query = Membership::find()->joinWith('user');
        $query->andWhere(['IN', 'group_id', [self::USERGROUP_ADMIN, self::USERGROUP_MODERATOR]]);
        $query->andWhere(['space_id' => $this->id]);
        $query->andWhere(['!=', 'user_id', $owner->id]);
        $query->andWhere(['user.status' => UserModel::STATUS_ENABLED]);

        foreach ($query->all() as $membership) {
            $groups[$membership->group_id][] = $membership->user;
        }

        return $groups;
    }

    /**
     * Gets query for [[GroupSpace]].
     *
     * @return \yii\db\ActiveQuery
     * @since 1.8
     */
    public function getGroupSpaces()
    {
        return $this->hasMany(GroupSpace::class, ['space_id' => 'id']);
    }
}
