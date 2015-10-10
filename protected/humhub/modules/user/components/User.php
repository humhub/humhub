<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

/**
 * Description of User
 *
 * @author luke
 */
class User extends \yii\web\User
{

    /**
     * @var PermissionManager
     */
    protected $permissionManager = null;

    public function isAdmin()
    {
        if ($this->isGuest)
            return false;

        return ($this->getIdentity()->super_admin == 1);
    }

    public function getLanguage()
    {
        if ($this->isGuest)
            return "";

        return $this->getIdentity()->language;
    }

    public function getTimeZone()
    {
        if ($this->isGuest)
            return "";

        return $this->getIdentity()->time_zone;
    }

    public function getGuid()
    {
        if ($this->isGuest)
            return "";

        return $this->getIdentity()->guid;
    }

    public function getPermissionManager()
    {
        if ($this->permissionManager !== null) {
            return $this->permissionManager;
        }

        $this->permissionManager = new PermissionManager;
        return $this->permissionManager;
    }

}
