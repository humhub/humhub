<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\models;

use humhub\components\behaviors\GUID;
use humhub\libs\UUIDValidator;
use humhub\modules\admin\permissions\ManageSpaces;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\components\ContentContainerSettingsManager;
use humhub\modules\content\models\Content;
use humhub\modules\space\activities\Created;
use humhub\modules\space\behaviors\SpaceController;
use humhub\modules\space\behaviors\SpaceModelMembership;
use humhub\modules\space\components\ActiveQuerySpace;
use humhub\modules\space\components\UrlValidator;
use humhub\modules\space\Module;
use humhub\modules\space\notifications\SpaceCreated;
use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\space\services\MemberListService;
use humhub\modules\user\behaviors\Followable;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\GroupSpace;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the model class for table "space".
 *
 * @property string $name
 * @property string $description
 * @property string $about
 * @property string $url
 * @property int $join_policy
 * @property int $status
 * @property int $sort_order
 * @property int $auto_add_new_members
 * @property int $default_content_visibility
 * @property string $color
 * @property User $ownerUser the owner of this space
 * @property-read AdvancedSettings $advancedSettings
 * @property-read mixed $applicants
 * @property-read GroupSpace[] $groupSpaces
 * @property-read Membership[] $memberships
 * @property-read mixed $nonMembershipUser
 * @property-read array $privilegedGroupUsers
 * @property-read array $searchAttributes
 *
 * @mixin GUID
 * @mixin SpaceModelMembership
 * @mixin Followable
 * @noinspection PropertiesInspection
 */
class Space extends ContentContainerActiveRecord
{
    // Join Policies
    public const JOIN_POLICY_NONE = 0; // No Self Join Possible
    public const JOIN_POLICY_APPLICATION = 1; // Invitation and Application Possible
    public const JOIN_POLICY_FREE = 2; // Free for All
    // Visibility: Who can view the space content.
    public const VISIBILITY_NONE = 0; // Private: This space is invisible for non-space-members
    public const VISIBILITY_REGISTERED_ONLY = 1; // Only registered users (no guests)
    public const VISIBILITY_ALL = 2; // Public: All Users (Members and Guests)
    // Status
    public const STATUS_DISABLED = 0;
    public const STATUS_ENABLED = 1;
    public const STATUS_ARCHIVED = 2;
    // UserGroups
    public const USERGROUP_OWNER = 'owner';
    public const USERGROUP_ADMIN = 'admin';
    public const USERGROUP_MODERATOR = 'moderator';
    public const USERGROUP_MEMBER = 'member';
    public const USERGROUP_USER = 'user';
    public const USERGROUP_GUEST = 'guest';
    // Model Scenarios
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_EDIT = 'edit';
    public const SCENARIO_SECURITY_SETTINGS = 'security_settings';

    /**
     * @inheritdoc
     */
    public $controllerBehavior = SpaceController::class;

    /**
     * @inheritdoc
     */
    public $defaultRoute = '/space/space';

    /**
     * @var AdvancedSettings|null
     */
    private $_advancedSettings = null;

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
            [['join_policy', 'visibility', 'status', 'sort_order', 'auto_add_new_members', 'default_content_visibility'], 'integer'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 45, 'min' => 2],
            [['description', 'about', 'color'], 'string'],
            [['tagsField', 'blockedUsersField'], 'safe'],
            [['description'], 'string', 'max' => 100],
            [['join_policy'], 'in', 'range' => [0, 1, 2]],
            [['visibility'], 'in', 'range' => [0, 1, 2]],
            [['visibility'], 'checkVisibility'],
            [['guid'], UUIDValidator::class],
            [['guid'], 'unique'],
            [['url'], UrlValidator::class, 'space' => $this],
        ];

        /** @var Module $module */
        $module = Yii::$app->getModule('space');

        if ($module->useUniqueSpaceNames) {
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

        $scenarios[static::SCENARIO_EDIT] = ['name', 'color', 'description', 'about', 'tagsField', 'blockedUsersField', 'join_policy', 'visibility', 'default_content_visibility'];
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

    /**
     * @inheritDoc
     */
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
            SpaceModelMembership::class,
            Followable::class,
        ];
    }

    /**
     * Returns advanced space settings
     *
     * @return AdvancedSettings
     */
    public function getAdvancedSettings(): AdvancedSettings
    {
        if ($this->_advancedSettings === null) {
            $this->_advancedSettings = new AdvancedSettings(['space' => $this]);
            $this->_advancedSettings->loadBySettings();
        }

        return $this->_advancedSettings;
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     * @throws \Throwable
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $user = User::findOne(['id' => $this->created_by]);

        if ($insert && $user) {
            // Auto add creator as admin
            $this->addMember($user->id, 1, true, self::USERGROUP_ADMIN);

            $activity = new Created();
            $activity->source = $this;
            $activity->originator = $user;
            $activity->create();

            // If the space creator is not allowed to manage spaces, notify space managers
            if (!(new PermissionManager(['subject' => $user]))->can(ManageSpaces::class)) {
                SpaceCreated::instance()->from($user)->about($this)->sendBulk(PermissionManager::findUsersByPermission(new ManageSpaces()));
            }
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

        if (empty($this->url)) {
            $this->url = new Expression('NULL');
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

        $this->getProfileImage()->delete();
        $this->getProfileBannerImage()->delete();

        Follow::deleteAll(['object_id' => $this->id, 'object_model' => static::class]);

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
     * @param $userId int|string|null User Id of User
     */
    public function canJoin($userId = null): bool
    {
        // Take current userId if none is given
        $userId ??= Yii::$app->user->id;

        if (!$userId) {
            return false;
        }

        // Checks if User is already a member
        if ($this->isMember($userId)) {
            return false;
        }

        if ($this->join_policy === self::JOIN_POLICY_NONE) {
            return false;
        }

        $user = Yii::$app->runtimeCache->getOrSet(User::class . '#' . $userId, function () use ($userId) {
            return User::findOne($userId);
        });

        if ($this->isBlockedForUser($user)) {
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
     * Archive this Space
     */
    public function archive()
    {
        $this->status = self::STATUS_ARCHIVED;
        $this->save(false); // disable validation to force archiving even if some fields are not valid such as too long description, as the archive button is not part of the space settings form and validation errors are not displayed
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
     * @return bool
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
     * @param string $attribute
     * @param string $params
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
    public function getDisplayName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getDisplayNameSub(): string
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

        /** @var Module $module */
        $module = Yii::$app->getModule('space');
        if ($module->globalAdminCanAccessPrivateContent && $user->isSystemAdmin()) {
            return true;
        }

        return ($this->isMember($user));
    }


    /**
     * Returns all Membership relations with status = STATUS_MEMBER.
     *
     * Be aware that this function will also include disabled users, in order to only include active and visible users use:
     *
     * ```
     * $this->getMemberListService()->getQuery()
     * ```
     *
     * @return ActiveQuery
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
     * @return ActiveQuery
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
            self::USERGROUP_USER => Yii::t('SpaceModule.base', 'Users'),
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

        $query = Membership::find()
            ->joinWith('user')
            ->with('user.profile')
            ->andWhere(['IN', 'group_id', [self::USERGROUP_ADMIN, self::USERGROUP_MODERATOR]])
            ->andWhere(['space_id' => $this->id])
            ->andWhere(['!=', 'user_id', $owner->id])
            ->andWhere(['user.status' => User::STATUS_ENABLED]);

        foreach ($query->all() as $membership) {
            $groups[$membership->group_id][] = $membership->user;
        }

        return $groups;
    }

    /**
     * Gets query for [[GroupSpace]].
     *
     * @return ActiveQuery
     * @since 1.8
     */
    public function getGroupSpaces()
    {
        return $this->hasMany(GroupSpace::class, ['space_id' => 'id']);
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isModuleEnabled($id)
    {
        return $this->moduleManager->isEnabled($id);
    }

    /**
     * @return MemberListService
     * @since 1.14
     */
    public function getMemberListService(): MemberListService
    {
        return new MemberListService($this);
    }
}
