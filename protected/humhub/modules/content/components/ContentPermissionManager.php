<?php

namespace humhub\modules\content\components;

use humhub\components\permission\AbstractPermissionManager;
use humhub\components\permission\BasePermission;
use humhub\modules\content\permissions\AbstractContentPermission;
use yii\base\InvalidArgumentException;

class ContentPermissionManager extends AbstractPermissionManager
{
    public ContentActiveRecord $model;

    protected function verify(BasePermission $permission)
    {
        /** @var AbstractContentPermission $permission */
        if (!($permission instanceof AbstractContentPermission)) {
            throw new InvalidArgumentException(get_class($permission) . ' must be instance of ' . AbstractContentPermission::class);
        }

        if (!isset($permission->model)) {
            $permission->model = $this->model;
        }

        return $permission->verify($this->getSubject());
    }
}
