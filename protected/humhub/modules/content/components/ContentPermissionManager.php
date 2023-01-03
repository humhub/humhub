<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\modules\content\Module;
use humhub\modules\content\permissions\ManageContent;
use humhub\modules\user\components\AbstractPermissionManager;
use Yii;

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
        if ($this->model->content->created_by == $user->id) {
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
     * @return bool
     */
    public function canMove(): bool
    {
        return $this->canEdit();
    }
}
