<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\components\Module;
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
    public $contentContainerClass = null;

    /**
     * @inheritdoc
     */
    protected function getModulePermissions(\yii\base\Module $module)
    {
        if ($module instanceof ContentContainerModule) {
            $containerPermissions = $module->getContainerPermissions(new $this->contentContainerClass);
            if (!empty($containerPermissions)) {
                // Don't try to find container permissions in the ContentContainerModule::getPermissions() below
                // since they are already defined in more proper method ContentContainerModule::getContainerPermissions()
                return $containerPermissions;
            }
        }

        // Try to find container permissions in the parent/general method Module::getPermissions()
        // because the module was not updated to use proper method ContentContainerModule::getContainerPermissions() yet
        if ($module instanceof Module) {
            return $module->getPermissions(new $this->contentContainerClass);
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    protected function createPermissionRecord()
    {
        $permission = new ContentContainerDefaultPermission;
        $permission->contentcontainer_class = $this->contentContainerClass;
        return $permission;
    }

    /**
     * @inheritdoc
     */
    protected function getQuery()
    {
        return \humhub\modules\content\models\ContentContainerDefaultPermission::find()
            ->where(['contentcontainer_class' => $this->contentContainerClass]);
    }

    /**
     * @inerhitdoc
     */
    public function setGroupState($groupId, $permission, $state)
    {
        parent::setGroupState($groupId, $permission, $state);
        // Clear default permissions cache after updating of each state:
        Yii::$app->cache->delete('defaultPermissions:' . $this->contentContainerClass);
    }

}
