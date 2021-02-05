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
        if ($module instanceof ContentContainerModule && method_exists($module, 'getContainerPermissions')) {
            $containerPermissions = $module->getContainerPermissions(new $this->contentcontainer_class);
            if (!empty($containerPermissions)) {
                // Don't try to find container permissions in the ContentContainerModule::getPermissions() below
                // since they are already defined in more proper method ContentContainerModule::getContainerPermissions()
                return $containerPermissions;
            }
        }

        // Try to find container permissions in the parent/general method Module::getPermissions()
        // because the module was not updated to use proper method ContentContainerModule::getContainerPermissions() yet
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
