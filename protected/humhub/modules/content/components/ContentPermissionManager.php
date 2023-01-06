<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\content\Module;
use humhub\modules\content\permissions\CreatePrivateContent;
use humhub\modules\content\permissions\CreatePublicContent;
use humhub\modules\content\permissions\ManageContent;
use humhub\modules\space\models\Space;
use humhub\modules\user\components\AbstractPermissionManager;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Exception;
use yii\db\IntegrityException;

/**
 * @inheritdoc
 * @since 1.14
 */
class ContentPermissionManager extends AbstractPermissionManager
{
    /**
     * @var ContentActiveRecord
     */
    public $model;

    /**
     * Holds an extra create permission by providing one of the following
     *
     *  - BasePermission class string
     *  - Array of type ['class' => '...', 'callback' => '...']
     *  - Anonymous function
     *  - BasePermission instance
     *
     * @var string permission instance
     */
    protected $createPermission = ManageContent::class;

    /**
     * Holds an extra manage permission by providing one of the following
     *
     *  - BasePermission class string
     *  - Array of type ['class' => '...', 'callback' => '...']
     *  - Anonymous function
     *  - BasePermission instance
     *
     * @var string permission instance
     */
    protected $managePermission = ManageContent::class;

    /**
     * @inheritdoc
     */
    public function can($permission, $params = [], $allowCaching = true)
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }

        $user = $this->getSubject();
        if (!$user) {
            return false;
        }

        // Owner always has an access to own created content
        if ($this->model->isOwner($user)) {
            return true;
        }

        /* @var $module Module */
        $module = Yii::$app->getModule('content');
        // Global Admin can edit/delete arbitrarily content
        if ($module->adminCanEditAllContent && $user->isSystemAdmin()) {
            return true;
        }

        return $this->model->content &&
            $this->model->content->container &&
            $this->model->content->container->getPermissionManager($user)->can($permission, $params, $allowCaching);
    }

    /**
     * @return bool
     */
    public function canAdd(): bool
    {
        return $this->model->isNewRecord && $this->can($this->createPermission);
    }

    /**
     * @return bool
     */
    public function canEdit(): bool
    {
        return !$this->model->isNewRecord && $this->can($this->managePermission);
    }

    /**
     * @return bool
     */
    public function canDelete(): bool
    {
        return $this->canEdit();
    }

    /**
     * Checks if user can view this content.
     *
     * @param User|integer|null $user
     * @return bool can view this content
     * @throws Exception
     * @throws \Throwable
     * @since 1.1
     */
    public function canView($user = null): bool
    {
        if (!$user && !Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();
        } else if (!$user instanceof User) {
            $user = User::findOne(['id' => $user]);
        }

        // Check global content visibility, private global content is visible for all users
        if (empty($this->model->content->contentcontainer_id) && !Yii::$app->user->isGuest) {
            return true;
        }

        // User can access own content
        if ($user !== null && $this->model->isOwner($user)) {
            return true;
        }

        // Check Guest Visibility
        if (!$user) {
            return $this->checkGuestAccess();
        }

        // Public visible content
        if ($this->model->content->isPublic()) {
            return true;
        }

        // Check system admin can see all content module configuration
        if ($user->canViewAllContent()) {
            return true;
        }

        return $this->model->content->isPrivate() &&
            $this->model->content->container !== null &&
            $this->model->content->container->canAccessPrivateContent($user);
    }

    /**
     * Determines if a guest user is able to read this content.
     * This is the case if all of the following conditions are met:
     *
     *  - The content is public
     *  - The `auth.allowGuestAccess` setting is enabled
     *  - The space or profile visibility is set to VISIBILITY_ALL
     *
     * @return bool
     */
    public function checkGuestAccess()
    {
        $isPublic = $this->model->content->isPublic();

        if (!$isPublic || !AuthHelper::isGuestAccessEnabled()) {
            return false;
        }

        // Global content
        if (!$this->model->content->container) {
            return $isPublic;
        }

        if ($this->model->content->container instanceof Space) {
            return $isPublic && $this->model->content->container->visibility == Space::VISIBILITY_ALL;
        }

        if ($this->model->content->container instanceof User) {
            return $isPublic && $this->model->content->container->visibility == User::VISIBILITY_ALL;
        }

        return false;
    }

    /**
     * Defines if this instance is movable and either returns true or a string indicating why the instance can't be moved.
     *
     * If a [[ContentContainerActiveRecord]] is given this function may adds container specific checks as permission
     * or visibility checks.
     *
     * Thus, instances may be movable but only to certain containers.
     *
     * @param ContentContainerActiveRecord|null $container the target container
     * @return bool|string either true in case the instance can be moved, otherwise a string indicating why the instance
     * can't be moved
     */
    public function canMove(ContentContainerActiveRecord $container = null)
    {
        $canModelBeMoved = $this->model->isMovable($container);
        if ($canModelBeMoved !== true) {
            return $canModelBeMoved;
        }

        if (!$container) {
            return $this->checkMovePermission() ? true : Yii::t('ContentModule.base', 'You do not have the permission to move this content.');
        }

        if ($container->contentcontainer_id === $this->model->content->contentcontainer_id) {
            return Yii::t('ContentModule.base', 'The content can\'t be moved to its current space.');
        }

        // Check if the related module is installed on the target space
        if (!$container->moduleManager->isEnabled($this->model->getModuleId())) {
            /* @var $module \humhub\components\Module */
            $module = Yii::$app->getModule($this->model->getModuleId());
            $moduleName = ($module instanceof ContentContainerModule) ? $module->getContentContainerName($container) : $module->getName();
            return Yii::t('ContentModule.base', 'The module {moduleName} is not enabled on the selected target space.', ['moduleName' => $moduleName]);
        }

        // Check if the current user is allowed to move this content at all
        if (!$this->checkMovePermission()) {
            return Yii::t('ContentModule.base', 'You do not have the permission to move this content.');
        }

        // Check if the current user is allowed to move this content to the given target space
        if (!$this->checkMovePermission($container)) {
            return Yii::t('ContentModule.base', 'You do not have the permission to move this content to the given space.');
        }

        // Check if the content owner is allowed to create content on the target space
        $ownerPermissions = $container->getPermissionManager($this->model->content->createdBy);
        if ($this->model->content->isPrivate() && !$ownerPermissions->can(CreatePrivateContent::class)) {
            return Yii::t('ContentModule.base', 'The author of this content is not allowed to create private content within the selected space.');
        }

        if ($this->model->content->isPublic() && !$ownerPermissions->can(CreatePublicContent::class)) {
            return Yii::t('ContentModule.base', 'The author of this content is not allowed to create public content within the selected space.');
        }

        return true;
    }

    /**
     * Checks if the current user has generally the permission to move this content on the given container or the current container if no container was provided.
     *
     * Note this function is only used for a general permission check use [[canMove()]] for a
     *
     * This is the case if:
     *
     * - The current user is the owner of this content
     * @param ContentContainerActiveRecord|null $container
     * @return bool determines if the current user is generally permitted to move content on the given container (or the related container if no container was provided)
     * @throws IntegrityException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function checkMovePermission(ContentContainerActiveRecord $container = null): bool
    {
        if (!$container) {
            $container = $this->model->content->container;
        }

        return $this->model->isOwner() || Yii::$app->user->can(ManageUsers::class) || $container->can(ManageContent::class);
    }

    /**
     * Checks if the current user can archive this content.
     * The content owner and the workspace admin can archive contents.
     *
     * @return bool
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function canArchive(): bool
    {
        // Currently global content can not be archived
        if (!$this->model->content->container) {
            return $this->canEdit();
        }

        // No need to archive content on an archived container, content is marked as archived already
        if ($this->model->content->container->isArchived()) {
            return false;
        }

        return $this->model->content->container->permissionManager->can(ManageContent::class);
    }

    /**
     * Checks if the user can lock comments for this content.
     *
     * @return bool
     * @throws Exception
     */
    public function canLockComments(): bool
    {
        if (!$this->model->content->container) {
            return $this->canEdit();
        }

        return $this->model->content->container->permissionManager->can(ManageContent::class);
    }

    /**
     * Checks if the user can pin this content.
     * This is only allowed for workspace owner.
     *
     * @return bool
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function canPin(): bool
    {
        // Currently global content can not be pinned
        if (!$this->model->content->container) {
            return false;
        }

        if ($this->model->content->isArchived()) {
            return false;
        }

        return $this->model->content->container->permissionManager->can(ManageContent::class);
    }
}
