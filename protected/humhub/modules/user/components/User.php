<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use Yii;
use yii\authclient\ClientInterface;
use humhub\modules\user\authclient\AuthClientHelpers;

/**
 * Description of User
 *
 * @author luke
 */
class User extends \yii\web\User
{

    /**
     * @var ClientInterface[] the users authclients
     */
    private $_authClients = null;

    /**
     * @var PermissionManager
     */
    protected $permissionManager = null;

    public function isAdmin()
    {
        if ($this->isGuest)
            return false;

        return $this->getIdentity()->isSystemAdmin();
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

    public function canChangePassword()
    {
        foreach ($this->getAuthClients() as $authClient) {
            if ($authClient->className() == \humhub\modules\user\authclient\Password::className()) {
                return true;
            }
        }

        return false;
    }

    public function canChangeEmail()
    {
        if (in_array('email', AuthClientHelpers::getSyncAttributesByUser($this->getIdentity()))) {
            return false;
        }

        return true;
    }

    public function canDeleteAccount()
    {
        foreach ($this->getAuthClients() as $authClient) {
            if ($authClient instanceof \humhub\modules\user\authclient\interfaces\AutoSyncUsers) {
                return false;
            }
        }
        return true;
    }

    public function getAuthClients()
    {
        if ($this->_authClients === null) {
            $this->_authClients = AuthClientHelpers::getAuthClientsByUser($this->getIdentity());
        }

        return $this->_authClients;
    }

    public function setCurrentAuthClient(ClientInterface $authClient)
    {
        Yii::$app->session->set('currentAuthClientId', $authClient->getId());
    }

    public function getCurrentAuthClient()
    {
        foreach ($this->getAuthClients() as $authClient) {
            if ($authClient->getId() == Yii::$app->session->get('currentAuthClientId')) {
                return $authClient;
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function afterLogin($identity, $cookieBased, $duration)
    {
        $identity->updateAttributes(['last_login' => new \yii\db\Expression('NOW()')]);

        parent::afterLogin($identity, $cookieBased, $duration);
    }

}
