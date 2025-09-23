<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\components\Module;
use humhub\modules\content\models\ContentContainerDefaultPermission;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\content\models\ContentContainerPermission;
use humhub\libs\BasePermission;
use Yii;

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
        if ($module instanceof Module) {
            return $module->getPermissions($this->contentContainer);
        }
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function createPermissionRecord()
    {
        $permission = new ContentContainerPermission();
        $permission->contentcontainer_id = $this->contentContainer->contentcontainer_id;
        return $permission;
    }

    /**
     * @inheritdoc
     */
    protected function getQuery()
    {
        return ContentContainerPermission::find()->where(['contentcontainer_id' => $this->contentContainer->contentcontainer_id]);
    }

    /**
     * Returns the group default state
     *
     * @param string $groupId
     * @param BasePermission $permission
     * @return string|int the state
     */
    public function getSingleGroupDefaultState($groupId, BasePermission $permission)
    {
        $defaultStoredState = $this->getDefaultStoredState($groupId, $permission);
        if ($defaultStoredState !== null) {
            return $defaultStoredState;
        }

        return $permission->getDefaultState($groupId);
    }

    /**
     * Returns the default state stored in DB per container type.
     * This method returns null in case the default state for this permission or group is not stored in DB yet.
     *
     * @param int $groupId
     * @param BasePermission $permission
     * @return int|null
     * @since 1.8
     */
    private function getDefaultStoredState($groupId, BasePermission $permission)
    {
        if ($this->contentContainer === null
            || !is_object($this->contentContainer)) {
            // Content Container must be defined to get default permission per column `contentcontainer_class`
            return null;
        }

        if ($this->contentContainer->isNewRecord) {
            // Exclude default permission of the Container,
            // in order to display the option "Default - Allow/Deny" from
            // config file/class and not from stored value in DB
            return null;
        }

        // Cache default permissions per Content Container Type(Space/User):
        $cachedDefaultPermissions = Yii::$app->cache->getOrSet('defaultPermissions:' . get_class($this->contentContainer), function () use ($groupId) {
            $records = ContentContainerDefaultPermission::find()
                ->select(['group_id', 'module_id', 'permission_id', 'state'])
                ->where(['contentcontainer_class' => get_class($this->contentContainer)])
                ->all();
            $defaultPermissions = [];
            foreach ($records as $defaultPermission) {
                /* @var $defaultPermission ContentContainerDefaultPermission */
                $defaultPermissions[$defaultPermission->group_id][$defaultPermission->module_id][$defaultPermission->permission_id] = $defaultPermission->state;
            }
            return $defaultPermissions;
        });

        if (isset($cachedDefaultPermissions[$groupId][$permission->getModuleId()][$permission->getId()])) {
            return (int)$cachedDefaultPermissions[$groupId][$permission->getModuleId()][$permission->getId()];
        }

        return null;
    }

}
