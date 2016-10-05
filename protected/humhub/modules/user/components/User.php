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
    
    /**
     * Shortcut for getPermisisonManager()->can().
     * 
     * Note: This method is used to verify global GroupPermissions and not ContentContainerPermissions.
     * 
     * @param mixed $permission
     * @see PermissionManager::can()
     * @return boolean
     * @since 1.2
     */
    public function can($permission, $params = [], $allowCaching = true)
    {
        // Compatibility with Yii2 base permission system.
        if(is_string($permission)) {
            return parent::can($permission, $params, $allowCaching);
        }
        
        return $this->getPermissionManager()->can($permission, $params, $allowCaching);
    }

    /**
     * @return PermissionManager instance with the related identity instance as permission subject.
     */
    public function getPermissionManager()
    {
        if ($this->permissionManager !== null) {
            return $this->permissionManager;
        }

        $this->permissionManager = new PermissionManager(['subject' => $this->getIdentity()]);
        return $this->permissionManager;
    }

    /**
     * Determines if this user is able to change the password.
     * @return boolean
     */
    public function canChangePassword()
    {
        foreach ($this->getAuthClients() as $authClient) {
            if ($authClient->className() == \humhub\modules\user\authclient\Password::className()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if this user is able to change the email address.
     * @return boolean
     */
    public function canChangeEmail()
    {
        if (in_array('email', AuthClientHelpers::getSyncAttributesByUser($this->getIdentity()))) {
            return false;
        }

        return true;
    }

    /**
     * Determines if this user is able to delete his account.
     * @return boolean
     */
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

}
