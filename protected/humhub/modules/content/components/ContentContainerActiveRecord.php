<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\components\ActiveRecord;
use humhub\libs\BasePermission;
use humhub\libs\ProfileBannerImage;
use humhub\libs\ProfileImage;
use humhub\libs\UUID;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\content\models\ContentContainerBlockedUsers;
use humhub\modules\content\models\ContentContainerTagRelation;
use humhub\modules\user\models\User;
use humhub\modules\user\Module as UserModule;
use Yii;
use yii\base\Component;
use yii\db\ActiveQuery;
use yii\helpers\Url;
use yii\web\IdentityInterface;

/**
 * ContentContainerActiveRecord for ContentContainer Models e.g. Space or User.
 *
 * Required Methods:
 *      - getProfileImage()
 *
 * @property int $id
 * @property int $visibility
 * @property string $guid
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 * @property int $contentcontainer_id
 * @property ContentContainer $contentContainerRecord
 * @property ContentContainerPermissionManager $permissionManager
 * @property ContentContainerSettingsManager $settings
 * @property-read string[] $blockedUserGuids
 * @property-read int[] $blockedUserIds
 * @property-read int $defaultContentVisibility
 * @property-read string $displayName
 * @property-read string|mixed $displayNameSub
 * @property-read ContentContainerModuleManager $moduleManager
 * @property-read ProfileBannerImage $profileBannerImage
 * @property-read ProfileImage $profileImage
 * @property-read string[] $tags
 * @property-read string $wallOut
 *
 * @since 1.0
 * @noinspection PropertiesInspection
 */
abstract class ContentContainerActiveRecord extends ActiveRecord
{
    /**
     * @var ContentContainerPermissionManager
     */
    protected $permissionManager = null;

    /**
     * @var ContentContainerModuleManager
     */
    private $_moduleManager = null;

    /**
     * The behavior which will be attached to the base controller.
     *
     * @since 1.3
     * @see ContentContainerController
     * @var string class name of additional the controller behavior
     */
    public $controllerBehavior = null;

    /**
     * @var string the default route
     */
    public $defaultRoute = '/';

    /**
     * @var array Related Tags which should be updated after save
     */
    public $tagsField;

    /**
     * @var array Related Blcoked Users IDs which should be updated after save
     */
    public $blockedUsersField;

    /**
     * @var string
     */
    public $profileImageClass = ProfileImage::class;

    /**
     * @var string
     */
    public $profileBannerImageClass = ProfileBannerImage::class;

    /**
     * Returns the display name of content container
     *
     * @return string
     * @since 0.11.0
     */
    abstract public function getDisplayName(): string;

    /**
     * Returns a descriptive sub title of this container used in the frontend.
     *
     * @return mixed
     * @since 1.4
     */
    abstract public function getDisplayNameSub(): string;

    /**
     * Returns the Profile Image Object for this Content Base
     *
     * @return ProfileImage
     */
    public function getProfileImage()
    {
        return new $this->profileImageClass($this);
    }

    /**
     * Returns the Profile Banner Image Object for this Content Base
     *
     * @return ProfileBannerImage
     */
    public function getProfileBannerImage()
    {
        return new $this->profileBannerImageClass($this);
    }

    /**
     * Should be overwritten by implementation
     * @param bool $scheme since 1.8
     * @return string
     */
    public function getUrl($scheme = false)
    {
        return $this->createUrl(null, [], $scheme);
    }

    /**
     * Creates url in content container scope.
     *
     * @param string $route
     * @param array $params
     * @param bool|string $scheme
     */
    public function createUrl($route = null, $params = [], $scheme = false)
    {
        array_unshift($params, ($route !== null) ? $route : $this->defaultRoute);
        $params['contentContainer'] = $this;

        return Url::to($params, $scheme);
    }

    /**
     * Checks if the user is allowed to access private content in this container
     *
     * @param User $user
     * @return bool can access private content
     */
    public function canAccessPrivateContent(User $user = null)
    {
        return false;
    }

    /**
     * Returns the wall output for this content container.
     * This is e.g. used in search results.
     *
     * @return string
     */
    public function getWallOut()
    {
        return "Default Wall Output for Class " . get_class($this);
    }

    /**
     * @param $token
     * @return ContentContainerActiveRecord|null
     */
    public static function findByGuid($token)
    {
        return static::findOne(['guid' => $token]);
    }

    /**
     * Compares this container with the given $container instance. If the $container is null this function will always
     * return false. Null values are accepted in order to safely enable calls as `$user->is(Yii::$app->user->getIdentity())`
     * which would otherwise fail in case of guest users.
     *
     * @param ContentContainerActiveRecord|null $container
     * @return bool
     * @since 1.7
     */
    public function is(ContentContainerActiveRecord $container = null)
    {
        if (!$container || !($container instanceof self)) {
            return false;
        }

        return $container->contentcontainer_id === $this->contentcontainer_id;
    }

    /**
     * @return ContentContainerSettingsManager
     */
    abstract public function getSettings(): ContentContainerSettingsManager;

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $contentContainer = new ContentContainer();
            $contentContainer->guid = $this->guid;
            $contentContainer->setPolymorphicRelation($this);

            if ($this instanceof User) {
                $contentContainer->owner_user_id = $this->id;
            } elseif ($this->hasAttribute('created_by')) {
                $contentContainer->owner_user_id = $this->created_by;
            }

            $contentContainer->save();
            $this->populateRelation('contentContainerRecord', $contentContainer);

            $this->contentcontainer_id = $contentContainer->id;
            $this->update(false, ['contentcontainer_id']);
        }

        if ($this->isAttributeSafe('tagsField') && $this->tagsField !== null) {
            ContentContainerTagRelation::updateByContainer($this, $this->tagsField);
        }

        if ($this->isAttributeSafe('blockedUsersField') && $this->blockedUsersField !== null) {
            ContentContainerBlockedUsers::updateByContainer($this, $this->blockedUsersField);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        ContentContainer::deleteAll([
            'pk' => $this->getPrimaryKey(),
            'class' => static::class,
        ]);

        parent::afterDelete();
    }

    /**
     * Returns the related ContentContainer model (e.g. Space or User)
     *
     * @return ActiveQuery
     * @see ContentContainer
     */
    public function getContentContainerRecord()
    {
        if ($this->hasAttribute('contentcontainer_id')) {
            return $this->hasOne(ContentContainer::class, ['id' => 'contentcontainer_id']);
        }

        return $this->hasOne(ContentContainer::class, ['pk' => 'id'])
            ->andOnCondition(['class' => get_class($this)]);
    }

    /**
     * Checks if the current user has the given Permission on this ContentContainerActiveRecord.
     * This is a shortcut for `$this->getPermisisonManager()->can()`.
     *
     * The following example will check if the current user has MyPermission on the given $contentContainer
     *
     * ```php
     * $contentContainer->can(MyPermisison::class);
     * ```
     *
     * Note: This method is used to verify ContentContainerPermissions and not GroupPermissions.
     *
     * @param string|string[]|BasePermission $permission
     * @return bool
     * @see PermissionManager::can()
     * @since 1.2
     */
    public function can($permission, $params = [], $allowCaching = true)
    {
        return $this->getPermissionManager()->can($permission, $params, $allowCaching);
    }

    /**
     * Returns a ContentContainerPermissionManager instance for this ContentContainerActiveRecord as permission object
     * and the given user (or current user if not given) as permission subject.
     *
     * @param User|IdentityInterface $user
     * @return ContentContainerPermissionManager
     */
    public function getPermissionManager(User $user = null)
    {
        if ($user && !$user->is(Yii::$app->user->getIdentity())) {
            return new ContentContainerPermissionManager([
                'contentContainer' => $this,
                'subject' => $user,
            ]);
        }

        if ($this->permissionManager !== null) {
            return $this->permissionManager;
        }

        return $this->permissionManager = new ContentContainerPermissionManager([
            'contentContainer' => $this,
        ]);
    }

    /**
     * Returns a ModuleManager
     *
     * @return ContentContainerModuleManager
     * @since 1.3
     */
    public function getModuleManager(): ?ContentContainerModuleManager
    {
        if ($this->_moduleManager !== null) {
            return $this->_moduleManager;
        }

        return $this->_moduleManager = new ContentContainerModuleManager([
            'contentContainer' => $this,
        ]);
    }

    /**
     * Returns user group for the given $user or current logged in user if no $user instance was provided.
     *
     * @param User|null $user
     * @return string
     */
    public function getUserGroup(User $user = null)
    {
        return "";
    }

    /**
     * Returns user groups
     */
    public static function getUserGroups()
    {
        return [];
    }

    /**
     * Returns weather or not the contentcontainer is archived. (Default false).
     * @return bool
     * @since 1.2
     */
    public function isArchived()
    {
        return false;
    }

    /**
     * Determines the default visibility of this container type.
     *
     * @return int
     */
    public function getDefaultContentVisibility()
    {
        return Content::VISIBILITY_PRIVATE;
    }

    /**
     * Checks the current visibility setting of this ContentContainerActiveRecord
     * @param $visibility
     * @return bool
     */
    public function isVisibleFor($visibility)
    {
        return $this->visibility == $visibility;
    }

    /**
     * Checks if the Content Container has Tags
     *
     * @return bool has tags set
     */
    public function hasTags()
    {
        return count($this->getTags()) > 0;
    }

    /**
     * Returns an array with related Tags
     *
     * @return string[] a list of tag names
     */
    public function getTags(): array
    {
        $tags = ($this->contentContainerRecord instanceof ContentContainer) && is_string($this->contentContainerRecord->tags_cached)
            ? trim($this->contentContainerRecord->tags_cached)
            : '';
        return $tags === '' ? [] : preg_split('/\s*,\s*/', $tags);
    }

    /**
     * Returns an array with GUIDs of the blocked users
     *
     * @return string[] a list of user GUIDs
     */
    public function getBlockedUserGuids(): array
    {
        return $this->allowBlockUsers() ? ContentContainerBlockedUsers::getGuidsByContainer($this) : [];
    }

    /**
     * Returns an array with IDs of the blocked user
     *
     * @return int[] a list of user IDs
     */
    public function getBlockedUserIds(): array
    {
        if (!$this->allowBlockUsers()) {
            return [];
        }

        $blockedUsers = $this->getSettings()->get(ContentContainerBlockedUsers::BLOCKED_USERS_SETTING);
        return empty($blockedUsers) ? [] : explode(',', $blockedUsers);
    }

    /**
     * Check if current container is blocked for the User
     *
     * @param User|null $user
     * @return bool
     */
    public function isBlockedForUser(?User $user = null): bool
    {
        if (!$this->allowBlockUsers()) {
            return false;
        }

        if ($user === null) {
            if (Yii::$app->user->isGuest) {
                return false;
            }

            $user = Yii::$app->user->getIdentity();
        }

        return in_array($user->id, $this->getBlockedUserIds());
    }

    /**
     * Check the blocking users is allowed by users module
     *
     * @return bool
     */
    public function allowBlockUsers(): bool
    {
        /* @var UserModule $userModule */
        $userModule = Yii::$app->getModule('user');

        return $userModule->allowBlockUsers();
    }

    /**
     * Block this container for the given or current User
     *
     * @param User|null $user
     * @return bool
     */
    public function blockForUser(?User $user = null): bool
    {
        if (!$this->allowBlockUsers()) {
            return false;
        }

        if ($user === null) {
            if (Yii::$app->user->isGuest) {
                return false;
            }
            $user = Yii::$app->user->getIdentity();
        }

        if ($user->isBlockedForUser($this)) {
            return true;
        }

        $newBlockedUserRelation = new ContentContainerBlockedUsers();
        $newBlockedUserRelation->contentcontainer_id = $user->contentcontainer_id;
        $newBlockedUserRelation->user_id = $this->id;
        if (!$newBlockedUserRelation->save()) {
            return false;
        }

        $blockedUserIds = $user->getBlockedUserIds();
        $blockedUserIds[] = $this->id;

        ContentContainerBlockedUsers::refreshCachedUserIds($user, $blockedUserIds);

        return true;
    }

    /**
     * Block the current container for the User
     *
     * @param User|null $user
     * @return bool
     */
    public function unblockForUser(?User $user = null): bool
    {
        if (!$this->allowBlockUsers()) {
            return false;
        }

        if ($user === null) {
            if (Yii::$app->user->isGuest) {
                return false;
            }
            $user = Yii::$app->user->getIdentity();
        }

        if (!$user->isBlockedForUser($this)) {
            return true;
        }

        $blockedUserRelation = ContentContainerBlockedUsers::findOne([
            'contentcontainer_id' => $user->contentcontainer_id,
            'user_id' => $this->id,
        ]);

        if (!$blockedUserRelation) {
            return true;
        }

        if (!$blockedUserRelation->delete()) {
            return false;
        }

        $blockedUserIds = $user->getBlockedUserIds();
        if (($deletedIndex = array_search($this->id, $blockedUserIds)) !== false) {
            unset($blockedUserIds[$deletedIndex]);
            ContentContainerBlockedUsers::refreshCachedUserIds($user, $blockedUserIds);
        }

        return true;
    }
}
