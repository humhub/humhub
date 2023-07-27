<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\components\ActiveRecord;
use humhub\libs\BasePermission;
use humhub\modules\content\controllers\ContainerImageController;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentBanner;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\content\models\ContentContainerBlockedUsers;
use humhub\modules\content\models\ContentContainerTagRelation;
use humhub\modules\content\models\ContentImage;
use humhub\modules\content\widgets\ContainerProfileHeader;
use humhub\modules\file\libs\FileControllerInterface;
use humhub\modules\file\models\AttachedImage;
use humhub\modules\file\models\AttachedImageIntermediateInterface;
use humhub\modules\file\models\AttachedImageOwnerInterface;
use humhub\modules\user\models\User;
use humhub\modules\user\Module as UserModule;
use Throwable;
use Yii;
use yii\helpers\Url;

/**
 * ContentContainerActiveRecord for ContentContainer Models e.g. Space or User.
 *
 * Required Methods:
 *      - getProfileImage()
 *
 * @property integer $id
 * @property integer $visibility
 * @property string $guid
 * @property integer $contentcontainer_id
 * @property ContentContainerPermissionManager $permissionManager
 * @property ContentContainerSettingsManager $settings
 * @property-read ContentContainerModuleManager $moduleManager
 * @property ContentContainer $contentContainerRecord
 *
 * @since 1.0
 * @author Luke
 */
abstract class ContentContainerActiveRecord extends ActiveRecord implements AttachedImageOwnerInterface
{
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
     * @var ContentImage|string
     */
    public $profileImageClass = ContentImage::class;
    protected ?AttachedImage $profileImage = null;

    /**
     * @var ContentBanner|string
     */
    public $profileBannerImageClass = ContentBanner::class;
    protected ?AttachedImage $profileBannerImage = null;

    public string $headerImageControllerClass;

    /**
     * @var ContentContainerPermissionManager
     */
    protected ?ContentContainerPermissionManager $permissionManager = null;

    /**
     * @var ContentContainerModuleManager
     */
    private $_moduleManager = null;

    protected string $headerImageUploadUrl = '/file/image/upload';
    protected string $headerImageCropUrl   = '/file/image/crop';
    protected string $headerImageDeleteUrl = '/file/image/delete';
    protected string $headerControlViewPath = '@content/widgets/views/profileHeaderControls.php';

    protected string $headerClassPrefix     = 'container';

    public static function tableName()
    {
        return 'contentcontainer';
    }

    public function getAttachedImage(array $config): ?\humhub\modules\file\models\AttachedImage
    {
        // TODO: Implement getAttachedImage() method.
    }

    public function getAttachedImages(): array
    {
        // TODO: Implement getAttachedImages() method.
    }

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
     * @return FileControllerInterface|string
     */
    public function getHeaderImageControllerClass(): string
    {
        return $this->headerImageControllerClass;
    }

    /**
     * Returns the Profile Banner Image Object for this Content Base
     *
     * @return ContentBanner|null
     */
    public function getProfileBannerImage(?string $defaultImage = null): ContentBanner
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->profileBannerImage ??= $this->profileBannerImageClass::findOneByRecord($this) ?? new $this->profileBannerImageClass($this, $defaultImage);
    }

    /**
     * Returns the Profile Image Object for this Content Base
     *
     * @return ContentImage|null
     */
    public function getProfileImage(?string $defaultImage = null): ?ContentImage
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->profileImage ??= $this->profileImageClass::findOneByRecord($this) ?? new $this->profileImageClass($this, $defaultImage);
    }


    /**
     * Should be overwritten by implementation
     *
     * @param bool $scheme since 0.5
     *
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
     * @param boolean|string $scheme
     */
    public function createUrl($route = null, $params = [], $scheme = false)
    {
        array_unshift($params, $route ?? $this->defaultRoute);
        $params['contentContainer'] = $this;

        return Url::to($params, $scheme);
    }

    /**
     * Checks if the user is allowed to access private content in this container
     *
     * @param User $user
     *
     * @return boolean can access private content
     */
    public function canAccessPrivateContent(User $user = null)
    {
        return false;
    }

    protected function canEditHeaderImages(): bool
    {
        return false;
    }

    public function initializeHeaderWidget(ContainerProfileHeader $header)
    {
        $header->imageUploadUrl    = $this->createUrlHeaderContainerImageUpload();
        $header->coverUploadUrl    = $this->createUrlHeaderCoverImageUpload();
        $header->coverCropUrl      = $this->createUrlHeaderCoverImageCrop();
        $header->imageCropUrl      = $this->createUrlHeaderContainerImageCrop();
        $header->imageDeleteUrl    = $this->createUrlHeaderContainerImageDelete();
        $header->coverDeleteUrl    = $this->createUrlHeaderCoverImageDelete();
        $header->headerControlView = $this->headerControlViewPath();
        $header->classPrefix       = $this->headerClassPrefix;

        // This is required in order to stay compatible with old themes...
        $controller = $this->getHeaderImageControllerClass();

        $config = [];

        if (is_subclass_of($controller, ContentContainerController::class)) {
            $config['contentContainer']             = $this;
            $config['validContentContainerClasses'] = [static::class];
        }

        $header->imageUploadName
            =
        $header->imageUploadName = $controller::getFileListParameterNameStatically(null, null, $config);
        $header->canEdit = $this->canEditHeaderImages();
    }

    protected function headerControlViewPath(): string
    {
        return $this->headerControlViewPath;
    }

    public function createUrlImageUpload(string $type = null): string
    {
        return $this->createUrl($this->headerImageUploadUrl, ['type' => $type]);
    }

    protected function createUrlHeaderCoverImageUpload(): string
    {
        return $this->createUrlImageUpload(ContainerImageController::TYPE_PROFILE_BANNER_IMAGE);
    }

    protected function createUrlHeaderContainerImageUpload(): string
    {
        return $this->createUrlImageUpload(ContainerImageController::TYPE_PROFILE_IMAGE);
    }

    public function createUrlImageView()
    {
    }

    public function createUrlImageCrop(string $type = null): string
    {
        return $this->createUrl($this->headerImageCropUrl, ['type' => $type]);
    }

    protected function createUrlHeaderContainerImageCrop(): string
    {
        return $this->createUrlImageCrop(ContainerImageController::TYPE_PROFILE_IMAGE);
    }

    protected function createUrlHeaderCoverImageCrop(): string
    {
        return $this->createUrlImageCrop(ContainerImageController::TYPE_PROFILE_BANNER_IMAGE);
    }

    public function createUrlImageDelete(string $type = null): string
    {
        return $this->createUrl($this->headerImageDeleteUrl, ['type' => $type]);
    }

    protected function createUrlHeaderCoverImageDelete(): string
    {
        return $this->createUrlImageDelete(ContainerImageController::TYPE_PROFILE_BANNER_IMAGE);
    }

    protected function createUrlHeaderContainerImageDelete(): string
    {
        return $this->createUrlImageDelete(ContainerImageController::TYPE_PROFILE_IMAGE);
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
     *
     * @return ContentContainerActiveRecord|null
     */
    public static function findByGuid($token)
    {
        return static::findOne(['guid' => $token]);
    }

    /**
     * Compares this container with the given $container instance. If the $container is null this function will always
     * return false. Null values are accepted in order to safely enable calls as
     * `$user->is(Yii::$app->user->getIdentity())` which would otherwise fail in case of guest users.
     *
     * @param ContentContainerActiveRecord|null $container
     *
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
            $contentContainer->class = static::class;
            $contentContainer->pk = $this->getPrimaryKey();
            if ($this instanceof User) {
                $contentContainer->owner_user_id = $this->id;
            } elseif ($this->hasAttribute('created_by')) {
                $contentContainer->owner_user_id = $this->created_by;
            }

            $contentContainer->save();

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
     * @return AttachedImageIntermediateInterface
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
     *
     * @return boolean
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
     * @param User|null $user
     *
     * @return ContentContainerPermissionManager
     * @throws Throwable
     */
    public function getPermissionManager(User $user = null)
    {
        if ($user && !$user->is(Yii::$app->user->getIdentity())) {
            return new ContentContainerPermissionManager([
                'contentContainer' => $this,
                'subject' => $user
            ]);
        }

        if ($this->permissionManager !== null) {
            return $this->permissionManager;
        }

        return $this->permissionManager = new ContentContainerPermissionManager([
            'contentContainer' => $this
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
            'contentContainer' => $this
        ]);
    }

    /**
     * Returns user group for the given $user or current logged in user if no $user instance was provided.
     *
     * @param User|null $user
     *
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
     * @return boolean
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
     *
     * @param $visibility
     *
     * @return bool
     */
    public function isVisibleFor($visibility)
    {
        return $this->visibility == $visibility;
    }

    /**
     * Checks if the Content Container has Tags
     *
     * @return boolean has tags set
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

        return $tags === ''
            ? []
            : preg_split('/\s*,\s*/', $tags);
    }

    /**
     * Returns an array with GUIDs of the blocked users
     *
     * @return string[] a list of user GUIDs
     */
    public function getBlockedUserGuids(): array
    {
        return $this->allowBlockUsers()
            ? ContentContainerBlockedUsers::getGuidsByContainer($this)
            : [];
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

        return empty($blockedUsers)
            ? []
            : explode(',', $blockedUsers);
    }

    /**
     * Check if current container is blocked for the User
     *
     * @param User|null $user
     *
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

}
