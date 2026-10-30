<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\helpers\DeviceDetectorHelper;
use humhub\helpers\MobileAppHelper;
use humhub\libs\BasePermission;
use humhub\modules\user\events\UserEvent;
use humhub\modules\user\models\User as UserModel;
use humhub\modules\user\services\AuthClientUserService;
use Throwable;
use Yii;
use yii\authclient\ClientInterface;
use yii\base\InvalidConfigException;
use yii\helpers\Url;

/**
 * Description of User
 *
 * @property UserModel|null $identity
 * @method  UserModel|null getIdentity(bool $autoRenew = true)
 * @property Impersonation $impersonation the impersonation state and configuration, see [[getImpersonation()]]
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

    private Impersonation|array $_impersonation = [];

    public function isAdmin(): bool
    {
        return !$this->isGuest
            && $this->getIdentity()->isSystemAdmin();
    }

    public function isGroupManager(): bool
    {
        return !$this->isGuest
            && $this->getIdentity()->isGroupManager();
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
        if ($this->isGuest) {
            return null;
        }

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

        // A regular login never continues an impersonation left over in the session
        $this->removeImpersonationMarker();

        parent::afterLogin($identity, $cookieBased, $duration);
    }

    /**
     * @inheritdoc
     */
    protected function afterLogout($identity)
    {
        if (DeviceDetectorHelper::isAppRequest()) {
            Yii::$app->session->set(MobileAppHelper::SESSION_VAR_SHOW_OPENER, 1);
        }

        // The session may survive the logout (auth timeout, pending single logout)
        $this->removeImpersonationMarker();

        parent::afterLogout($identity);
    }

    private function removeImpersonationMarker(): void
    {
        if ($this->enableSession && Yii::$app->has('session')) {
            Yii::$app->session->remove(Impersonation::SESSION_KEY);
        }
    }

    /**
     * @return Impersonation the impersonation state and configuration of this user component
     * @since 1.19
     */
    public function getImpersonation(): Impersonation
    {
        if (!$this->_impersonation instanceof Impersonation) {
            $this->_impersonation = Yii::createObject(array_merge(
                ['class' => Impersonation::class],
                $this->_impersonation,
                ['user' => $this],
            ));
        }

        return $this->_impersonation;
    }

    /**
     * @param array $config the [[Impersonation]] configuration, e.g. `['allowPrivateContentAccess' => true]`
     * @since 1.19
     */
    public function setImpersonation(array $config)
    {
        $this->_impersonation = $config;
    }

    /**
     * @return int duration in seconds of the current auto-login (remember me) cookie, `0` when there is none
     * @since 1.19
     */
    public function getAuthCookieDuration(): int
    {
        if (!$this->enableAutoLogin || Yii::$app->request->isConsoleRequest) {
            return 0;
        }

        $cookie = $this->getIdentityAndDurationFromCookie();

        return empty($cookie['duration']) ? 0 : (int)$cookie['duration'];
    }

    /**
     * @inheritdoc
     */
    public function switchIdentity($identity, $duration = 0)
    {
        $this->trigger(self::EVENT_BEFORE_SWITCH_IDENTITY, new UserEvent(['user' => $identity]));

        if (empty($duration) && !$this->getImpersonation()->isActive()) {
            // Keep the auto-login duration across identity switches, but never issue an auto-login
            // cookie for an impersonated identity: it would outlive the impersonation state in the
            // session and resurrect the impersonated identity as a regular login.
            $duration = $this->getAuthCookieDuration();
        }

        parent::switchIdentity($identity, $duration);
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
     *
     * The own flow page of a registered user gate (e.g. `/twofa/check`) is never stored as the
     * return URL: the gate page redirects to the return URL once the gate is closed, which would
     * be the gate page itself (endless redirect loop). A previously stored return URL is dropped
     * as well, so the user lands on the home page after login.
     *
     * @since 1.19.3
     */
    public function setReturnUrl($url)
    {
        if ($this->isGateUrl($url)) {
            Yii::$app->getSession()->remove($this->returnUrlParam);
            return;
        }

        parent::setReturnUrl($url);
    }

    /**
     * @inheritdoc
     *
     * Ignores the stored return URL if it points to a user gate page, e.g. in sessions that
     * stored it before [[setReturnUrl()]] filtered it, and falls back to `$defaultUrl` or the home URL.
     *
     * @since 1.19.3
     */
    public function getReturnUrl($defaultUrl = null)
    {
        $url = parent::getReturnUrl($defaultUrl);

        if (!$this->isGateUrl($url)) {
            return $url;
        }

        return ($defaultUrl !== null && !$this->isGateUrl($defaultUrl))
            ? Url::to($defaultUrl)
            : Yii::$app->getHomeUrl();
    }

    private function isGateUrl($url): bool
    {
        // Not before this instance is registered as `Yii::$app->user`: a `returnUrl` configured on
        // the component is applied while it is still being created, and a gate reading
        // `Yii::$app->user` in its route (e.g. MustChangePasswordGate) would re-enter the
        // application's `__get()` ("Undefined property: Application::$user").
        return (is_string($url) || is_array($url))
            && Yii::$app->has('user', true)
            && Yii::$app->has('gateManager')
            && Yii::$app->gateManager->isGateUrl($url);
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

        if (
            $this->enableSession
            && Yii::$app->request->isPjax
            && Yii::$app->request->isGet
            && (!$checkAcceptHeader || $this->checkRedirectAcceptable())
        ) {
            $queryParams = Yii::$app->request->getQueryParams();
            unset($queryParams['_pjax'], $queryParams['_']);

            $returnUrl = '/' . Yii::$app->request->getPathInfo();
            if (!empty($queryParams)) {
                $returnUrl .= '?' . http_build_query($queryParams);
            }

            $this->setReturnUrl(Url::to($returnUrl, true));
            $checkAjax = true;
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
