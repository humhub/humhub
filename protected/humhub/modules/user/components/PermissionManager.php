<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use Yii;
use humhub\libs\BasePermission;
use humhub\modules\user\models\GroupPermission;

/**
 * Description of PermissionManager
 *
 * @author luke
 */
class PermissionManager extends \yii\base\Component
{

    protected $permissions = null;

    public function can(BasePermission $permission)
    {
        $groupId = Yii::$app->user->getIdentity()->group_id;
        if ($this->getGroupState($groupId, $permission) == BasePermission::STATE_ALLOW) {
            return true;
        }

        return false;
    }

    /**
     * Sets the state for a group
     * 
     * @param string $groupId
     * @param BasePermission $permission
     * @param string $state
     */
    public function setGroupState($groupId, BasePermission $permission, $state)
    {
        $record = $this->getGroupStateRecord($groupId, $permission);

        // No need to store default state
        if ($state === '' || $state === null) {
            if ($record !== null) {
                $record->delete();
            }
            return;
        }

        if ($record === null) {
            $record = $this->createPermissionRecord();
        }

        $record->permission_id = $permission->id;
        $record->module_id = $permission->moduleId;
        $record->class = $permission->className();
        $record->group_id = $groupId;
        $record->state = $state;
        $record->save();
    }

    /**
     * Returns the group state
     * 
     * @param string $groupId
     * @param BasePermission $permission
     * @param boolean $returnDefaultState
     * @return string the state
     */
    public function getGroupState($groupId, BasePermission $permission, $returnDefaultState = true)
    {
        // Check if database entry exists
        $dbRecord = $this->getGroupStateRecord($groupId, $permission);

        if ($dbRecord !== null) {
            return $dbRecord->state;
        }

        if ($returnDefaultState) {
            return $permission->getDefaultState($groupId);
        }

        return "";
    }

    /**
     * Returns a BasePermission by Id
     * 
     * @param string $permissionId
     * @param string $moduleId
     * @return BasePermission
     */
    public function getById($permissionId, $moduleId)
    {
        $module = Yii::$app->getModule($moduleId);

        foreach ($this->getModulePermissions($module) as $permission) {
            if ($permission->hasId($permissionId)) {
                return $permission;
            }
        }

        return null;
    }

    protected function getGroupStateRecord($groupId, BasePermission $permission)
    {
        return $this->getQuery()->andWhere([
                    'group_id' => $groupId,
                    'module_id' => $permission->moduleId,
                    'permission_id' => $permission->id
                ])->one();
    }

    /**
     * Returns a list of all Permission objects
     * 
     * @return array of BasePermissions
     */
    public function getPermissions()
    {
        if ($this->permissions !== null) {
            return $this->permissions;
        }

        $this->permissions = [];

        // Loop over all active modules
        foreach (Yii::$app->getModules() as $id => $module) {
            // Ensure module is instanciated
            $module = Yii::$app->getModule($id);

            $this->permissions = array_merge($this->permissions, $this->getModulePermissions($module));
        }

        return $this->permissions;
    }

    /**
     * Returns permissions provided by a module
     * 
     * @param \yii\base\Module $module
     * @return array of BasePermissions
     */
    protected function getModulePermissions(\yii\base\Module $module)
    {
        if ($module instanceof \humhub\components\Module) {
            return $module->getPermissions();
        }

        return [];
    }

    /**
     * Creates a Permission Database record
     * 
     * @return Permission
     */
    protected function createPermissionRecord()
    {
        return new GroupPermission;
    }

    /**
     * Creates a Permission Database Query
     * 
     * @return \yii\db\ActiveQuery
     */
    protected function getQuery()
    {
        return GroupPermission::find();
    }

    /**
     * Returns Permission Array
     * 
     * @param type $groupId
     * @return type
     */
    public function createPermissionArray($groupId)
    {
        $permissions = [];
        foreach ($this->getPermissions() as $permission) {
            $permissions[] = [
                'id' => $permission->id,
                'title' => $permission->title,
                'description' => $permission->description,
                'moduleId' => $permission->moduleId,
                'permissionId' => $permission->id,
                'states' => [
                    BasePermission::STATE_DEFAULT => BasePermission::getLabelForState('') . ' - ' . BasePermission::getLabelForState($permission->getDefaultState($groupId)),
                    BasePermission::STATE_DENY => BasePermission::getLabelForState(BasePermission::STATE_DENY),
                    BasePermission::STATE_ALLOW => BasePermission::getLabelForState(BasePermission::STATE_ALLOW),
                ],
                'changeable' => $permission->canChangeState($groupId),
                'state' => $this->getGroupState($groupId, $permission, false),
            ];
        }
        return $permissions;
    }

}
