<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\modules\user\components\PermissionManager;
use humhub\modules\content\models\ContentContainerDefaultPermission;
use Yii;

/**
 * @inheritdoc
 */
class ContentContainerDefaultPermissionManager extends PermissionManager
{

    /**
     * @var string
     */
    public $contentcontainer_class = null;

    /**
     * @inheritdoc
     */
    protected function getModulePermissions(\yii\base\Module $module)
    {
        if ($module instanceof \humhub\components\Module) {
            return $module->getPermissions(new $this->contentcontainer_class);
        }
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function createPermissionRecord()
    {
        $permission = new ContentContainerDefaultPermission;
        $permission->contentcontainer_class = $this->contentcontainer_class;
        return $permission;
    }

    /**
     * @inheritdoc
     */
    protected function getQuery()
    {
        return \humhub\modules\content\models\ContentContainerDefaultPermission::find()
            ->where(['contentcontainer_class' => $this->contentcontainer_class]);
    }

    /**
     * @inerhitdoc
     */
    public function setGroupState($groupId, $permission, $state)
    {
        parent::setGroupState($groupId, $permission, $state);
        // Clear default permissions cache after updating of each state:
        Yii::$app->cache->delete('defaultPermissions:'.$this->contentcontainer_class);
    }

}
