<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\helpers\DeviceDetectorHelper;
use humhub\libs\BasePermission;
use humhub\modules\user\events\UserEvent;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User as UserModel;
use humhub\modules\user\services\AuthClientUserService;
use Throwable;
use Yii;
use yii\authclient\ClientInterface;
use yii\base\InvalidConfigException;

/**
 * Description of User
 *
 * @property UserModel|null $identity
 * @method  UserModel|null getIdentity(bool $autoRenew = true)
 * @mixin Impersonator
 * @author luke
 */
class User extends \yii\web\User
{
    public const EVENT_BEFORE_SWITCH_IDENTITY = 'beforeSwitchIdentity';

    /**
     * @var PermissionManager
     */
    protected $permissionManager = null;

    /**
     * @var string Route to force user to change password
     * @since 1.8
     */
    public $mustChangePasswordRoute = '/user/must-change-password';

    private ?AuthClientUserService $authClientUserService = null;

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
     *
     * @return bool
     * @throws InvalidConfigException
     * @throws Throwable
     * @since 1.2
     * @see PermissionManager::can()
     */
    public function can($permission, $params = [], $allowCaching = true)
    {
        return $this->getPermissionManager()->can($permission, $params, $allowCaching);
    }

    /**
     * @return PermissionManager instance with the related identity instance as permission subject.
     * @throws Throwable
     */
    public function getPermissionManager()
    {
        if ($this->permissionManager !== null) {
            return $this->permissionManager;
        }
        $this->permissionManager = new PermissionManager(['subject' => $this->getIdentity()]);

        return $this->permissionManager;
    }

    public function setCurrentAuthClient(ClientInterface $authClient)
    {
        Yii::$app->session->set('currentAuthClientId', $authClient->getId());
    }

    public function getCurrentAuthClient()
    {
        foreach ($this->getAuthClientUserService()->getClients() as $authClient) {
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
     * @return bool is guest access enabled and allowed
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
     * @return bool
     * @deprecated since 1.14
     */
    public function canDeleteAccount()
    {
        return ($this->getAuthClientUserService())->canDeleteAccount();
    }

    /**
     * @return bool Check if current page is already URL to forcing user to change password
     * @since 1.8
     */
    public function isMustChangePasswordUrl()
    {
        return Yii::$app->requestedRoute === trim($this->mustChangePasswordRoute, '/');
    }

    /**
     * Determines if this user must change the password.
     *
     * @return bool
     * @since 1.8
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
        if (DeviceDetectorHelper::isMicrosoftOffice()) {
            Yii::$app->response->setStatusCode(200);
            Yii::$app->response->data = Yii::$app->controller->htmlRedirect(Yii::$app->request->getAbsoluteUrl());
            return Yii::$app->getResponse();
        }

        return parent::loginRequired($checkAjax, $checkAcceptHeader);
    }

    public function getAuthClientUserService(): AuthClientUserService
    {
        if ($this->authClientUserService === null) {
            $this->authClientUserService = new AuthClientUserService($this->identity);
        }

        return $this->authClientUserService;
    }
}
