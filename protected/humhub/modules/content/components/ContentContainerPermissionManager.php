<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\components\Module as Module;
use humhub\libs\BasePermission;
use humhub\modules\content\models\ContentContainerDefaultPermission;
use humhub\modules\content\models\ContentContainerPermission;
use humhub\modules\user\components\PermissionManager;
use Yii;
use yii\base\Module as BaseModule;

/**
 * @inheritdoc
 */
class ContentContainerPermissionManager extends PermissionManager
{
    /**
     * @var ContentContainerActiveRecord|null
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
    protected function getModulePermissions(BaseModule $module)
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
        $permission = Yii::createObject(ContentContainerPermission::class);
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
     * @param string|int $groupId
     * @param BasePermission $permission
     *
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
     * @param string|int $groupId
     * @param BasePermission $permission
     *
     * @return int|null
     * @since 1.8
     */
    private function getDefaultStoredState($groupId, BasePermission $permission)
    {
        if ($this->contentContainer === null) {
            // Content Container must be defined to get default permission per column `contentcontainer_class`
            return null;
        }

        if ($this->contentContainer->isNewRecord) {
            // Exclude default permission of the Container,
            // in order to display the option "Default - Allow/Deny" from
            // config file/class and not from stored value in DB
            return null;
        }

        $class = get_class($this->contentContainer);
        // Cache default permissions per Content Container Type(Space/User):
        $cachedDefaultPermissions = Yii::$app->cache->getOrSet(
            'defaultPermissions:' . $class,
            static function () use ($class): array {
                $records = ContentContainerDefaultPermission::find()
                    ->select(['group_id', 'module_id', 'permission_id', 'state',])
                    ->where(['contentcontainer_class' => $class])
                    ->all();
                $defaultPermissions = [];
                foreach ($records as $defaultPermission) {
                    /* @var $permissionRecord ContentContainerDefaultPermission */
                    $defaultPermissions[$defaultPermission->group_id][$defaultPermission->module_id][$defaultPermission->permission_id]
                        = $defaultPermission->state;
                }

                return $defaultPermissions;
            }
        );

        if (isset($cachedDefaultPermissions[$groupId][$permission->moduleId][get_class($permission)])) {
            return (int)$cachedDefaultPermissions[$groupId][$permission->moduleId][get_class($permission)];
        }

        return null;
    }
}
