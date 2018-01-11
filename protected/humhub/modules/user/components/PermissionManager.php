<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\components\Module;
use humhub\libs\BasePermission;
use humhub\modules\user\models\GroupPermission;
use Yii;
use yii\base\Component;

/**
 * Description of PermissionManager
 *
 * @author luke
 */
class PermissionManager extends Component
{

    /**
     * User identity.
     * @var \humhub\modules\user\models\User
     */
    public $subject;

    /**
     * Cached Permission array.
     * @var array
     */
    protected $permissions = null;

    /**
     * Permission access cache.
     * @var array
     */
    protected $access = [];

    /**
     * Verifies a given $permission or $permission array for a permission subject.
     *
     * If $params['strict'] is set to true and a $permission array is given all given permissions
     * have to be granted otherwise (default) only one permission test has to pass.
     *
     * @param string|array|BasePermission $permission
     * @param array $params
     * @param boolean $allowCaching
     * @return boolean
     * @throws \yii\base\InvalidConfigException
     */
    public function can($permission, $params = [], $allowCaching = true)
    {
        if (is_string($permission) || $permission instanceof BasePermission) {
            return $this->check($permission);
        }

        if (is_array($permission)) {
            $temp = array_filter($permission, function ($item) use ($allowCaching) {
                return $this->check($item, $allowCaching);
            });

            if ($this->isVerifyAll($params) === true && count($temp) === count($permission)) {
                return true;
            }

            if ($this->isVerifyAll($params) === false && count($temp) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifies a given $permission for a permission subject.
     *
     * @param string|BasePermission $permission
     * @param bool $allowCaching
     * @return bool|mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function check($permission, $allowCaching = true)
    {
        if (is_string($permission)) {
            $permission = Yii::createObject($permission);
        }

        if ($allowCaching && isset($this->access[$permission->getId()])) {
            return $this->access[$permission->getId()];
        }

        $result = $this->verify($permission);

        if ($allowCaching) {
            $this->access[$permission->getId()] = $result;
        }

        return $result;
    }

    /**
     * Return boolean for verifyAll
     *
     * @param array $params
     * @return bool
     */
    private function isVerifyAll($params = [])
    {
        if (isset($params['strict'])) {
            return $params['strict'];
        }

        //deprecated
        if (isset($params['all'])) {
            return $params['all'];
        }

        return false;
    }

    /**
     * Verifies a single permission for a given permission subject.
     *
     * @param BasePermission $permission
     * @return boolean
     */
    protected function verify(BasePermission $permission)
    {
        $subject = $this->getSubject();
        if ($subject) {
            return $this->getGroupState($subject->groups, $permission) == BasePermission::STATE_ALLOW;
        }

        return false;
    }

    /**
     * Returns the permission subject identity.
     * If the permission objects $subject property is not set this method returns the currently
     * logged in user identity.
     *
     * @return \humhub\modules\user\models\User
     */
    protected function getSubject()
    {
        return ($this->subject != null) ? $this->subject : Yii::$app->user->getIdentity();
    }

    /**
     * Clears access cache
     */
    public function clear()
    {
        $this->access = [];
    }

    /**
     * Sets the state for a given groupId.
     *
     * @param string $groupId
     * @param string|BasePermission $permission either permission class or instance
     * @param string $state
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function setGroupState($groupId, $permission, $state)
    {
        $permission = (is_string($permission)) ? Yii::createObject($permission) : $permission;
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

        $record->permission_id = $permission->getId();
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
     * @param mixed $groups either an array of groups or group ids or an single group or goup id
     * @param BasePermission $permission
     * @param int $returnDefaultState
     * @return int
     */
    public function getGroupState($groups, BasePermission $permission, $returnDefaultState = 1)
    {
        if (is_array($groups)) {
            $state = "";
            foreach ($groups as $group) {
                $state = $this->getSingleGroupState($group, $permission, $returnDefaultState);
                if ($state === BasePermission::STATE_ALLOW) {
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
        if ($groupId instanceof \humhub\modules\user\models\Group) {
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
     * @return mixed|null
     * @throws \yii\base\InvalidConfigException
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

    /**
     * @param $groupId
     * @param BasePermission $permission
     * @return array|null|\yii\db\ActiveRecord
     */
    protected function getGroupStateRecord($groupId, BasePermission $permission)
    {
        return $this->getQuery()->andWhere([
                    'group_id' => $groupId,
                    'module_id' => $permission->moduleId,
                    'permission_id' => $permission->getId()
                ])->one();
    }

    /**
     * Returns a list of all Permission objects
     *
     * @return array of BasePermissions
     * @throws \yii\base\InvalidConfigException
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
     * @param Module $module
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    protected function getModulePermissions(Module $module)
    {
        $result = [];
        if ($module instanceof Module) {
            $permisisons = $module->getPermissions();
            if (!empty($permisisons)) {
                foreach ($permisisons as $permission) {
                    $result[] = is_string($permission) ? Yii::createObject($permission) : $permission;
                }
            }
        }

        return $result;
    }

    /**
     * Creates a Permission Database record
     *
     * @return \yii\db\ActiveRecord
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
     * @param int $groupId v
     * @param bool $returnOnlyChangeable
     * @return array the permission array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function createPermissionArray($groupId, $returnOnlyChangeable = false)
    {

        $permissions = [];
        foreach ($this->getPermissions() as $permission) {
            if ($returnOnlyChangeable && !$permission->canChangeState($groupId)) {
                continue;
            }

            $permissions[] = [
                'id' => $permission->id,
                'title' => $permission->title,
                'description' => $permission->description,
                'moduleId' => $permission->moduleId,
                'permissionId' => $permission->id,
                'states' => [
                    BasePermission::STATE_DEFAULT => BasePermission::getLabelForState(BasePermission::STATE_DEFAULT) . ' - ' . BasePermission::getLabelForState($permission->getDefaultState($groupId)),
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
