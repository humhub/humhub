<?php

namespace humhub\modules\content\permissions;

use humhub\components\permission\BasePermission;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\user\models\User;

abstract class AbstractContentPermission extends BasePermission
{
    abstract public function verify(ContentActiveRecord $content, ?User $user): bool;
}
