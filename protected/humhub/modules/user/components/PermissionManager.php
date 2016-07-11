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
        $groups = Yii::$app->user->getIdentity()->groups;
        if ($this->getGroupState($groups, $permission) == BasePermission::STATE_ALLOW) {
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
     * Returns the group permission state of the given group or goups.
     * If the provided $group is an array we check if one of the group states
     * is a BasePermission::STATE_ALLOW and return this state.
     * 
     * @param type $groups either an array of groups or group ids or an single group or goup id
     * @param BasePermission $permission
     * @param type $returnDefaultState
     * @return type
     */
    public function getGroupState($groups, BasePermission $permission, $returnDefaultState = true)
    {
        if(is_array($groups)) {
            $state = "";
            foreach($groups as $group) {
                $state = $this->getSingleGroupState($group, $permission, $returnDefaultState);
                if($state === BasePermission::STATE_ALLOW) {
                    return $state;
                }
            }
            return $state;
        }
        return $this->getSingleGroupState($groups, $permission, $returnDefaultState);
    }

    /**
     * Returns the group state
     * 
     * @param string $groupId
     * @param BasePermission $permission
     * @param boolean $returnDefaultState
     * @return string the state
     */
    private function getSingleGroupState($groupId, BasePermission $permission, $returnDefaultState = true)
    {
        if($groupId instanceof \humhub\modules\user\models\Group) {
            $groupId = $groupId->id;
        }
        
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
