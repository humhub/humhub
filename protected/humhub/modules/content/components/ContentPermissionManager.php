<?php

namespace humhub\modules\content\components;

use humhub\components\permission\AbstractPermissionManager;
use humhub\components\permission\BasePermission;
use humhub\modules\content\permissions\AbstractContentPermission;
use yii\base\InvalidArgumentException;

class ContentPermissionManager extends AbstractPermissionManager
{

    public $content;

    protected function verify(BasePermission $permission)
    {
        if (!($permission instanceof AbstractContentPermission)) {
            throw new InvalidArgumentException(get_class($permission) . ' must be instance of ' . AbstractContentPermission::class);
        }



        // TODO: Implement verify() method.
    }
}
