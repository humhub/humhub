<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\modules\user\authclient\AuthClientHelpers;
use humhub\modules\user\authclient\Password;
use humhub\modules\user\authclient\interfaces\AutoSyncUsers;
use humhub\modules\user\events\UserEvent;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User as UserModel;
use Yii;
use yii\authclient\ClientInterface;

/**
 * Description of User
 * @property UserModel|null $identity
 * @mixin Impersonator
 * @author luke
 */
class User extends \yii\web\User
{

    const EVENT_BEFORE_SWITCH_IDENTITY = 'beforeSwitchIdentity';

    /**
     * @var ClientInterface[] the users authclients
     */
    private $_authClients = null;

    /**
     * @var PermissionManager
     */
    protected $permissionManager = null;

    /**
     * @var string Route to force user to change password
     * @since 1.8
     */
    public $mustChangePasswordRoute = '/user/must-change-password';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            Impersonator::class,
        ];
    }

    public function isAdmin()
    {
        if ($this->isGuest) {
            return false;
        }

        return $this->getIdentity()->isSystemAdmin();
    }

    public function getLanguage()
    {
        if ($this->isGuest) {
            return '';
        }

        return $this->getIdentity()->language;
    }

    public function getTimeZone()
    {
        if ($this->isGuest) {
            return '';
        }

        return $this->getIdentity()->time_zone;
    }

    public function getGuid()
    {
        if ($this->isGuest) {
            return '';
        }

        return $this->getIdentity()->guid;
    }

    /**
     * Verifies global GroupPermissions of this User component.
     *
     * The following example checks if this User is granted the GroupPermission
     *
     * ```php
     * if(Yii::$app->user->can(MyGroupPermission::class) {
     *   // ...
     * }
     * ```
     *
     * @param string|string[]|BasePermission $permission
     * @return boolean
     * @throws \yii\base\InvalidConfigException
     * @throws \Throwable
     * @since 1.2
     * @see PermissionManager::can()
     */
    public function can($permission, $params = [], $allowCaching = true)
    {
        return $this->getPermissionManager()->can($permission, $params, $allowCaching);
    }

    /**
     * @return PermissionManager instance with the related identity instance as permission subject.
     * @throws \Throwable
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
            if ($authClient->className() == Password::class) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if this user is able to change the email address.
     * @return boolean
     * @throws \Throwable
     */
    public function canChangeEmail()
    {
        if (in_array('email', AuthClientHelpers::getSyncAttributesByUser($this->getIdentity()))) {
            return false;
        }

        return true;
    }

    /**
     * Determines if this user is able to change his username.
     * @return boolean
     * @throws \Throwable
     */
    public function canChangeUsername()
    {
        if (in_array('username', AuthClientHelpers::getSyncAttributesByUser($this->getIdentity()))) {
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
            if ($authClient instanceof AutoSyncUsers) {
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
        $identity->updateAttributes(['last_login' => date('Y-m-d G:i:s')]);

        parent::afterLogin($identity, $cookieBased, $duration);
    }

    /**
     * Checks if the system configuration allows access for guests
     *
     * @return boolean is guest access enabled and allowed
     * @deprecated since 1.4
     */
    public static function isGuestAccessEnabled()
    {
        return AuthHelper::isGuestAccessEnabled();
    }

    /**
     * @inheritdoc
     */
    public function switchIdentity($identity, $duration = 0)
    {
        $this->trigger(self::EVENT_BEFORE_SWITCH_IDENTITY, new UserEvent(['user' => $identity]));

        if (empty($duration) && !Yii::$app->request->isConsoleRequest) {
            // Try to use login duration from the current session, e.g. on impersonate action
            $cookie = $this->getIdentityAndDurationFromCookie();
            $duration = empty($cookie['duration']) ? 0 : $cookie['duration'];
        }

        parent::switchIdentity($identity, $duration);
    }

    /**
     * @since 1.8
     * @return bool Check if current page is already URL to forcing user to change password
     */
    public function isMustChangePasswordUrl()
    {
        return Yii::$app->requestedRoute === trim($this->mustChangePasswordRoute, '/');
    }

    /**
     * Determines if this user must change the password.
     * @since 1.8
     * @return boolean
     */
    public function mustChangePassword()
    {
        return !$this->isGuest && $this->getIdentity() && $this->getIdentity()->mustChangePassword();
    }

    /**
     * @inheritdoc
     */
    public function loginRequired($checkAjax = true, $checkAcceptHeader = true)
    {
        // Fix 4700: Handle Microsoft Office Probe Requests
        if (strpos(Yii::$app->request->getUserAgent(), 'Microsoft Office') !== false) {
            Yii::$app->response->setStatusCode(200);
            Yii::$app->response->data = Yii::$app->controller->htmlRedirect(Yii::$app->request->getAbsoluteUrl());
            return Yii::$app->getResponse();
        }

        return parent::loginRequired($checkAjax, $checkAcceptHeader);
    }
}