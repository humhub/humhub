<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\libs\BasePermission;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\content\models\ContentContainerDefaultPermission;

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
     * @inerhitdoc
     */
    public function createPermissionArray($groupId, $returnOnlyChangeable = false)
    {
        $permissions = parent::createPermissionArray($groupId, $returnOnlyChangeable);

        foreach ($permissions as $p => $permission) {
            /** @var $permission BasePermission */
            if ($permission['contentContainer'] === null) {
                // Force to don't allow changing of default permission if module still
                // doesn't initialize the permission with Content Container properly
                $permissions[$p]['changeable'] = false;
            }
        }

        return $permissions;
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

}
