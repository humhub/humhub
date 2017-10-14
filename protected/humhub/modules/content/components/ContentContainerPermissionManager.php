<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\modules\user\components\PermissionManager;
use humhub\modules\content\models\ContentContainerPermission;
use humhub\libs\BasePermission;

/**
 * @inheritdoc
 */
class ContentContainerPermissionManager extends PermissionManager
{

    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer = null;

    /**
     * @inheritdoc
     */
    public function verify(BasePermission $permission)
    {
        $groupId = $this->contentContainer->getUserGroup($this->subject);

        if ($this->getGroupState($groupId, $permission) == BasePermission::STATE_ALLOW) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    protected function getModulePermissions(\yii\base\Module $module)
    {
        if ($module instanceof \humhub\components\Module) {
            return $module->getPermissions($this->contentContainer);
        }
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function createPermissionRecord()
    {
        $permission = new ContentContainerPermission;
        $permission->contentcontainer_id = $this->contentContainer->contentcontainer_id;
        return $permission;
    }

    /**
     * @inheritdoc
     */
    protected function getQuery()
    {
        return \humhub\modules\content\models\ContentContainerPermission::find()->where(['contentcontainer_id' => $this->contentContainer->contentcontainer_id]);
    }

}
