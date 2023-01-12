<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\permission;

use humhub\components\Module;
use Yii;
use yii\base\Component;
use yii\base\Module as BaseModule;


abstract class AbstractPermissionManager extends Component
{

    /**
     * Permission access cache.
     * @var array
     */
    protected $_access = [];

    /**
     * User identity.
     * @var \humhub\modules\user\models\User
     */
    public $subject;

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

        if (is_array($permission)) {
            // compatibility for old 'all' param
            $verifyAll = $this->isVerifyAll($params);
            foreach ($permission as $current) {
                $can = $this->can($current, $params, $allowCaching);
                if ($can && !$verifyAll) {
                    return true;
                } elseif (!$can && $verifyAll) {
                    return false;
                }
            }
            return $verifyAll;
        } elseif ($allowCaching) {
            $permission = ($permission instanceof BasePermission) ? $permission : Yii::createObject($permission);
            $key = $permission->getId();

            if (!isset($this->_access[$key])) {
                $this->_access[$key] = $this->verify($permission);
            }

            return $this->_access[$key];
        } else {
            $permission = ($permission instanceof BasePermission) ? $permission : Yii::createObject($permission);
            return $this->verify($permission);
        }
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
    abstract protected function verify(BasePermission $permission);

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
     * Returns a BasePermission by Id
     *
     * @param string $permissionId
     * @param string $moduleId
     * @return BasePermission|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getById($permissionId, $moduleId)
    {
        $module = Yii::$app->getModule($moduleId);

        foreach ($this->getModulePermissions($module) as $permission) {
            /** @var BasePermission $permission */
            if ($permission->hasId($permissionId)) {
                return $permission;
            }
        }

        return null;
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
     * @param BaseModule $module
     * @return array of BasePermissions
     * @throws \yii\base\InvalidConfigException
     */
    protected function getModulePermissions(BaseModule $module)
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
     * Clears access cache
     */
    public function clear()
    {
        $this->_access = [];
    }


}



