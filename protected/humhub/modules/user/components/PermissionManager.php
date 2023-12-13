<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\components\Module;
use humhub\components\PermissionActiveRecord;
use humhub\libs\BasePermission;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupPermission;
use humhub\modules\user\models\User as UserModel;
use Throwable;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\Module as BaseModule;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

/**
 * Description of PermissionManager
 *
 * @property-read ActiveQuery $query
 */
class PermissionManager extends Component
{
    /**
     * User identity.
     *
     * @var UserModel|null
     */
    public $subject;

    /**
     * Cached Permission array.
     *
     * @var array|null
     */
    protected $permissions = null;

    /**
     * Permission access cache.
     *
     * @var array
     */
    protected $_access = [];

    /**
     * Cache for permission group states, array is divided into sub array for easier access
     * map: [{group_id} => [GroupPermission, ...]]
     *
     * @var PermissionActiveRecord[]
     */
    protected $_groupPermissions = [];

    /**
     * Verifies a given $permission or $permission array for a permission subject.
     *
     * If $params['strict'] is set to true and a $permission array is given all given permissions
     * have to be granted otherwise (default) only one permission test has to pass.
     *
     * @param string|array|BasePermission $permission
     * @param array $params
     * @param boolean $allowCaching
     *
     * @return boolean
     * @throws InvalidConfigException
     */
    public function can($permission, array $params = [], bool $allowCaching = true)
    {

        if (is_array($permission)) {
            // compatibility for old 'all' param
            $verifyAll = $this->isVerifyAll($params);
            foreach ($permission as $current) {
                $can = $this->can($current, $params, $allowCaching);

                if ($can && !$verifyAll) {
                    return true;
                }

                if (!$can && $verifyAll) {
                    return false;
                }
            }

            return $verifyAll;
        }

        $permission = $permission instanceof BasePermission
            ? $permission
            : Yii::createObject($permission);

        if ($allowCaching) {
            $key = $permission->getId();

            return $this->_access[$key] ??= $this->verify($permission, $params);
        }

        return $this->verify($permission);
    }

    /**
     * Return boolean for verifyAll
     *
     * @param array $params
     *
     * @return bool
     */
    private function isVerifyAll(array $params = [])
    {
        return $params['strict'] ?? $params['all'] ?? false;
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
     * @return UserModel|null
     */
    protected function getSubject()
    {
        return $this->subject ?? Yii::$app->user->getIdentity();
    }

    /**
     * Clears access cache
     */
    public function clear()
    {
        $this->_access = [];
        $this->_groupPermissions = [];

        Yii::$app->runtimeCache->flush();        // ToDo: Flush only PermissionManager related entries
    }

    /**
     * Sets the state for a given groupId.
     *
     * @param int|string $groupId
     * @param string|BasePermission $permission either permission class or instance
     * @param int|null $state
     *
     * @throws Throwable
     * @throws InvalidConfigException
     * @throws StaleObjectException
     */
    public function setGroupState($groupId, $permission, ?int $state)
    {
        $permission = is_string($permission)
            ? Yii::createObject($permission)
            : $permission;

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
        $record->module_id = $permission->getModuleId();
        $record->class = get_class($permission);
        $record->group_id = $groupId;
        $record->state = $state;
        if ($record->save()) {
            $this->clear();
        }
    }

    /**
     * Returns the group permission state of the given group or groups.
     * If the provided `$group` is an array, we check if one of the group states
     * is a BasePermission::STATE_ALLOW and return this state.
     *
     * @param mixed $groups either an array of groups or group ids or a single group or group id
     * @param BasePermission $permission
     * @param bool $returnDefaultState
     *
     * @return int
     */
    public function getGroupState($groups, BasePermission $permission, bool $returnDefaultState = true): ?int
    {
        $this->prefetchGroups($groups);

        if (is_array($groups)) {
            $state = null;
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
     * Prefetch permissions for groups, use getPrefetchedStateRecord to get prefetched permission
     *
     * @param $groups
     */
    protected function prefetchGroups($groups)
    {
        if (!$ids = $this->prefetchGatherGroupIds($groups)) {
            return;
        }

        // array_fill_keys is done in order to record group ids, even if no records found
        // recorded group id will not be fetched again
        $this->_groupPermissions += array_fill_keys($ids, []);

        $result = Yii::$app->runtimeCache->getOrSet(__METHOD__ . implode(',', $ids), function () use ($ids) {
            return $this->getQuery()->andWhere(['group_id' => $ids])->all();
        });

        foreach ($result as $group) {
            /** @var GroupPermission | ActiveRecord $group */
            $this->_groupPermissions[$group->group_id][] = $group;
        }
    }

    /**
     * Gets ids from groups array (or string) for the query,
     * prefetched groups ignored and not wil not be included in the array
     *
     * @param Group[]|Group|int[]|int|string[]|string $groups
     * @param array $ids
     *
     * @return array
     */
    protected function prefetchGatherGroupIds($groups, array $ids = [])
    {
        if (!is_array($groups)) {
            $groups = [$groups];
        }

        foreach ($groups as $group) {
            /** @var Group | string | int $groupIds */
            $id = $group instanceof Group
                ? $group->id
                : $group;

            if (isset($this->_groupPermissions[$id])) {
                continue;
            }

            $ids[] = $id;
        }

        return $ids;
    }

    /**
     * Try to get permission from the prefetched array
     *
     * @param int|string $groupId
     * @param BasePermission $permission
     *
     * @return GroupPermission|null
     */
    protected function getPrefetchedStateRecord($groupId, BasePermission $permission)
    {
        if (empty($this->_groupPermissions[$groupId])) {
            return null;
        }

        foreach ($this->_groupPermissions[$groupId] as $groupPermission) {
            /** @var $groupPermission GroupPermission */
            if (
                $groupPermission->permission_id == $permission->getId()
                && $groupPermission->module_id == $permission->getModuleId()
            ) {
                return $groupPermission;
            }
        }

        return null;
    }

    /**
     * Returns the group state
     *
     * @param Group|int|string $groupId
     * @param BasePermission $permission
     * @param boolean $returnDefaultState
     *
     * @return int|null the state
     */
    private function getSingleGroupState($groupId, BasePermission $permission, bool $returnDefaultState = true)
    {
        if ($groupId instanceof Group) {
            $groupId = $groupId->id;
        }

        if ($cached = $this->getPrefetchedStateRecord($groupId, $permission)) {
            return $cached->state;
        }

        if ($returnDefaultState) {
            return $this->getSingleGroupDefaultState($groupId, $permission);
        }

        return null;
    }

    /**
     * Returns the group default state
     *
     * @param int|string $groupId
     * @param BasePermission $permission
     *
     * @return int the state
     */
    protected function getSingleGroupDefaultState($groupId, BasePermission $permission)
    {
        return $permission->getDefaultState($groupId);
    }

    /**
     * Returns a BasePermission by Id
     *
     * @param string $permissionId
     * @param string $moduleId
     *
     * @return BasePermission|null
     * @throws InvalidConfigException
     */
    public function getById(string $permissionId, string $moduleId)
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
     * Not used anymore, permissions are now prefetched into $_groupPermissions array
     *
     * @param int|string $groupId
     * @param BasePermission $permission
     *
     * @return array|null|\yii\db\ActiveRecord
     * @deprecated since 1.10
     * @see        static::$_groupPermissions
     */
    protected function getGroupStateRecord($groupId, BasePermission $permission)
    {
        return $this->getQuery()->andWhere([
            'group_id' => $groupId,
            'module_id' => $permission->getModuleId(),
            'permission_id' => $permission->getId()
        ])->one();
    }

    /**
     * Returns a list of all Permission objects
     *
     * @return BasePermission[] of BasePermissions
     * @throws InvalidConfigException
     */
    public function getPermissions()
    {
        if ($this->permissions !== null) {
            return $this->permissions;
        }

        $this->permissions = [];

        // Loop over all active modules
        foreach (Yii::$app->getModules() as $id => $module) {
            // Ensure module is instantiated
            $module = Yii::$app->getModule($id);

            $this->permissions = array_merge($this->permissions, $this->getModulePermissions($module));
        }

        return $this->permissions;
    }

    /**
     * Returns permissions provided by a module
     *
     * @param BaseModule $module
     *
     * @return BasePermission[] of BasePermissions
     * @throws InvalidConfigException
     */
    protected function getModulePermissions(BaseModule $module)
    {
        $result = [];
        if ($module instanceof Module) {
            $permissions = $module->getPermissions();
            if (!empty($permissions)) {
                foreach ($permissions as $permission) {
                    $result[] = is_string($permission)
                        ? Yii::createObject($permission)
                        : $permission;
                }
            }
        }

        return $result;
    }

    /**
     * Creates a Permission Database record
     *
     * @return PermissionActiveRecord
     * @noinspection PhpDocMissingThrowsInspection
     */
    protected function createPermissionRecord()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return Yii::createObject(GroupPermission::class);
    }

    /**
     * Creates a Permission Database Query
     *
     * @return ActiveQuery
     */
    protected function getQuery()
    {
        return GroupPermission::find();
    }

    /**
     * Returns Permission Array
     *
     * @param int|string $groupId id of the group
     * @param bool $returnOnlyChangeable
     *
     * @return array[] = [
     *     'id' => string,
     *     'title' = string,
     *     'description' => string,
     *     'moduleId' => string,
     *     'permissionId' => string',
     *     'states' => [
     *                 int => string
     *     ],
     *     'changeable' => bool,
     *     'state' => int,
     *     'contentContainer' => ContentContainerActiveRecord|null
     * ] the permission array
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function createPermissionArray($groupId, $returnOnlyChangeable = false)
    {

        $permissions = [];
        foreach ($this->getPermissions() as $permission) {
            if ($returnOnlyChangeable && !$permission->canChangeState($groupId)) {
                continue;
            }

            $defaultState = BasePermission::getLabelForState(BasePermission::STATE_DEFAULT) . ' - '
                . BasePermission::getLabelForState($this->getSingleGroupDefaultState($groupId, $permission));

            $permissions[] = [
                'id' => $permission->getId(),
                'title' => $permission->getTitle(),
                'description' => $permission->getDescription(),
                'moduleId' => $permission->getModuleId(),
                'permissionId' => $permission->getId(),
                'states' => [
                    BasePermission::STATE_DEFAULT => $defaultState,
                    BasePermission::STATE_DENY => BasePermission::getLabelForState(BasePermission::STATE_DENY),
                    BasePermission::STATE_ALLOW => BasePermission::getLabelForState(BasePermission::STATE_ALLOW),
                ],
                'changeable' => $permission->canChangeState($groupId),
                'state' => $this->getGroupState($groupId, $permission, false),
                'contentContainer' => $permission->contentContainer,
            ];
        }

        return $permissions;
    }

    /**
     * Returns a query for users which are granted given permission
     *
     * @param BasePermission $permission
     *
     * @return ActiveQueryUser
     * @since 1.3.8
     */
    public static function findUsersByPermission(BasePermission $permission)
    {
        $pm = new static();

        $allowedGroupIds = [];
        foreach (Group::find()->all() as $group) {
            if ($pm->getGroupState($group, $permission) == BasePermission::STATE_ALLOW) {
                $allowedGroupIds[] = $group->id;
            }
        }

        return UserModel::find()
            ->joinWith('groupUsers')
            ->andWhere(['IN', 'group_user.group_id', $allowedGroupIds]);
    }
}
