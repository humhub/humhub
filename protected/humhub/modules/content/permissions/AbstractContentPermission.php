<?php

namespace humhub\modules\content\permissions;

use humhub\components\permission\BasePermission;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\user\models\User;

/**
 * @property-read Content $content
 * @property-read ContentContainerActiveRecord $container
 */
abstract class AbstractContentPermission extends BasePermission
{
    public ContentActiveRecord $model;

    abstract public function verify(?User $user = null): bool;

    public function getContent(): ?Content
    {
        return $this->model instanceof ContentActiveRecord ? $this->model->content : null;
    }

    public function getContainer(): ?ContentContainerActiveRecord
    {
        return $this->content instanceof Content ? $this->content->container : null;
    }
}
