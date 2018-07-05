<?php

namespace humhub\modules\directory\permissions;

use humhub\libs\BasePermission;
use Yii;

class AccessDirectory extends BasePermission
{
    /**
     * @inheritdoc
     */
    protected $moduleId = 'directory';

    /**
     * @inheritdoc
     */
    protected $defaultState = self::STATE_ALLOW;

    public function getTitle()
    {
        return Yii::t('DirectoryModule.base', 'Access directory');
    }

    public function getDescription()
    {
        return Yii::t('DirectoryModule.base', 'Can access the directory section.');
    }
}
