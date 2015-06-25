<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\core\user\components;

/**
 * Description of User
 *
 * @author luke
 */
class User extends \yii\web\User
{

    public function isAdmin()
    {
        if ($this->isGuest)
            return false;

        return ($this->getIdentity()->super_admin == 1);
    }

    /**
     *
     * @deprecated since version 1
     * @return \humhub\core\user\models\User
     */
    public function getModel()
    {
        return $this->getIdentity();
    }

    public function getLanguage()
    {
        if ($this->isGuest)
            return "";

        return $this->getIdentity()->language;
    }

    public function getGuid()
    {
        if ($this->isGuest)
            return "";

        return $this->getIdentity()->guid;
    }

}
