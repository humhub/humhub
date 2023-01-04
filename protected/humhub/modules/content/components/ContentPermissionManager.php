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
use humhub\modules\user\components\AbstractPermissionManager;
use Yii;
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
}
