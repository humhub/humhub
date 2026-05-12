<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\components\Response;
use humhub\helpers\DeviceDetectorHelper;
use humhub\modules\user\authclient\BaseFormAuth;
use humhub\modules\user\authclient\interfaces\SerializableAuthClient;
use humhub\modules\user\events\UserEvent;
use humhub\modules\user\models\forms\LoginIdentity;
use humhub\modules\user\models\forms\LoginPassword;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\Session;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use humhub\modules\user\services\AuthClientService;
use humhub\modules\user\services\InviteRegistrationService;
use humhub\modules\user\services\LinkRegistrationService;
use humhub\modules\user\services\UserSourceService;
use Throwable;
use Yii;
use yii\authclient\BaseClient;
use yii\base\Exception;
use yii\web\Cookie;
use yii\web\HttpException;

/**
 * AuthController handles login and logout
 *
 * @since 0.5
 *
 * @property Module $module
 */
class AuthController extends Controller
{
    /**
     * @event Triggered after an successful login. Note: In contrast to User::EVENT_AFTER_LOGIN, this event is triggered
     * after the response is generated.
     */
    public const EVENT_AFTER_LOGIN = 'afterLogin';

    /**
     * @event Triggered after an successful login but before checking user status
     */
    public const EVENT_BEFORE_CHECKING_USER_STATUS = 'beforeCheckingUserStatus';

    /**
     * Session key holding the username submitted on Step 1 while waiting for
     * the password to be entered on Step 2. Cleared on Step 2 success and on
     * any GET hit on the Step 1 URL (so the back arrow / browser back resets
     * the flow without exposing the username in the URL).
     */
    private const SESSION_KEY_STEP1_USERNAME = 'auth.login.step1.username';

    /**
     * Cookie holding the username/email when the user opted into "Remember
     * login name" on Step 2. Drives the auto-skip-to-Step-2 behaviour on the
     * next visit. 30 days lifetime; cleared by the back arrow on Step 2 (via
     * ?forget=1) or when the user opts out on the next successful sign-in.
     */
    private const COOKIE_REMEMBER_USERNAME = 'auth.login.rememberUsername';
    private const COOKIE_REMEMBER_USERNAME_LIFETIME = 2592000;

    /**
     * @inheritdoc
     */
    public $layout = '@humhub/modules/user/views/layouts/main';

    /**
     * Allow guest access independently from guest mode setting.
     *
     * @var string
     */
    public $access = ControllerAccess::class;

    /**
     * @inheritdoc
     */
    protected $doNotInterceptActionIds = ['*'];

    /**
     * Whether self-registration is currently permitted at all — false in
     * maintenance mode or when the admin has disabled anonymous registration.
     * Covers the back-end gate: blocks AuthClient-driven auto-registration and
     * the public Sign-Up form alike.
     *
     * Single source of truth for the `auth.anonymousRegistration` setting;
     * views/widgets/models call this instead of reading the setting directly.
     *
     * @since 1.19
     */
    public static function isSelfRegistrationEnabled(): bool
    {
        return !Yii::$app->settings->get('maintenanceMode')
            && (bool)Yii::$app->getModule('user')->settings->get('auth.anonymousRegistration');
    }

    /**
     * Whether the public Sign-Up email form / Sign-Up entry-points should be
     * rendered. False when self-registration is globally off, or when the admin
     * disabled the form-based public sign-up via {@see Module::$showRegistrationForm}
     * (e.g. SSO-only deployments where SAML/OIDC may auto-register but no
     * public form is offered).
     *
     * @since 1.19
     */
    public static function isSelfRegistrationFormVisible(): bool
    {
        return self::isSelfRegistrationEnabled()
            && Yii::$app->getModule('user')->showRegistrationForm;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'external' => [
                'class' => \yii\authclient\AuthAction::class,
                'successCallback' => $this->onAuthSuccess(...),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        // Allow automated logout requests from mobile app
        if ($action->id === 'logout' && DeviceDetectorHelper::isAppRequest()) {
            $this->enableCsrfValidation = false;
        }

        // Remove authClient from session - if already exists
        Yii::$app->session->remove('authClient');

        return parent::beforeAction($action);
    }

    /**
     * Step 1 of the login flow — collects the username/email only.
     *
     * On POST success the username is stashed in the session and the request is
     * redirected to {@see actionPassword()} (POST-Redirect-GET). On GET the
     * session is cleared, so navigating back to /user/auth/login from anywhere
     * always presents a fresh Step 1.
     */
    public function actionLogin()
    {
        // If user is already logged in, redirect him to the dashboard
        if (!Yii::$app->user->isGuest) {
            return $this->goBack();
        }

        // Explicit "Use a different account" / back-arrow click on Step 2:
        // drop the remembered-username cookie and the in-flight Step-2 state
        // so the user lands on a fresh Step 1.
        if (Yii::$app->request->get('forget') !== null) {
            Yii::$app->session->remove(self::SESSION_KEY_STEP1_USERNAME);
            Yii::$app->response->cookies->remove(self::COOKIE_REMEMBER_USERNAME);
            return $this->redirect(['/user/auth/login']);
        }

        // Maintenance mode gate: render dedicated view unless the request
        // explicitly asks for the admin-login form via ?maintenanceAdmin=1.
        // The actual admin-only enforcement happens post-auth in
        // {@see onAuthSuccess()} — the param only controls which view we
        // render here, it grants no privilege.
        if (Yii::$app->settings->get('maintenanceMode')
            && !Yii::$app->request->get('maintenanceAdmin')) {
            Yii::$app->session->remove(self::SESSION_KEY_STEP1_USERNAME);
            return $this->render('maintenance');
        }

        // Auto-skip Step 1 when the user previously opted into "Remember
        // login name". Only on a fresh GET (POST means they're submitting
        // Step 1 explicitly with an entered username — respect that).
        // Maintenance gate above already short-circuited; the cookie only
        // matters once the platform is open again.
        if (Yii::$app->request->isGet) {
            $rememberedUsername = Yii::$app->request->cookies->getValue(self::COOKIE_REMEMBER_USERNAME);
            if (is_string($rememberedUsername) && $rememberedUsername !== '') {
                Yii::$app->session->set(self::SESSION_KEY_STEP1_USERNAME, $rememberedUsername);
                return $this->redirect(['/user/auth/password']);
            }
        }

        $loginParams = [
            'signUpAllowed' => self::isSelfRegistrationFormVisible(),
            'showLoginForm' => $this->module->showLoginForm || Yii::$app->request->get('showLoginForm', false),
        ];

        $login = new LoginIdentity();

        if ($login->load(Yii::$app->request->post()) && $login->validate()) {
            $redirect = $login->getStep1Redirect();
            if ($redirect !== null) {
                Yii::$app->session->remove(self::SESSION_KEY_STEP1_USERNAME);
                // For AJAX/modal flow, force a real browser navigation to the
                // external auth endpoint via htmlRedirect — otherwise the
                // OAuth handshake would happen inside the XHR pipeline and
                // the state cookie set on /user/auth/external wouldn't be
                // associated with the eventual provider callback.
                return Yii::$app->request->isAjax
                    ? $this->htmlRedirect($redirect)
                    : $this->redirect($redirect);
            }
            Yii::$app->session->set(self::SESSION_KEY_STEP1_USERNAME, $login->username);

            // Modal/AJAX: render Step 2 inline so the modal swaps content
            // instead of triggering a 302 redirect (the modal client doesn't
            // navigate on X-Redirect, and the URL bar isn't visible to the
            // user inside a modal anyway).
            if (Yii::$app->request->isAjax) {
                return $this->renderPasswordStep();
            }

            // Page flow: POST-Redirect-GET so Step 2 lands as a clean GET page.
            // Username travels via session, not URL.
            return $this->redirect(['/user/auth/password']);
        }

        // Any non-submitting hit on the Step 1 URL resets the in-flight
        // Step-2 state. (Step-1 POST that fails validation keeps it cleared
        // anyway — there is nothing to preserve.)
        if (!Yii::$app->request->isPost) {
            Yii::$app->session->remove(self::SESSION_KEY_STEP1_USERNAME);
        }

        if (Yii::$app->settings->get('maintenanceMode')) {
            Yii::$app->session->setFlash('error', Yii::t('UserModule.auth', 'Maintenance mode is active.'));
        }

        $params = array_merge($loginParams, ['model' => $login]);

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('login_modal', $params);
        }

        return $this->render('login', $params);
    }

    /**
     * Step 2 of the login flow — collects the password for the username
     * stashed in the session by {@see actionLogin()}.
     *
     * If the session holds no Step-1 username (direct hit, expired session,
     * back-arrow click) the request is redirected back to Step 1.
     *
     * @since 1.19
     */
    public function actionPassword()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goBack();
        }

        $username = (string)Yii::$app->session->get(self::SESSION_KEY_STEP1_USERNAME, '');
        if ($username === '') {
            return $this->redirect(['/user/auth/login']);
        }

        $login = new LoginPassword();
        $login->username = $username;

        if ($login->load(Yii::$app->request->post())) {
            // Force the username from the session — never trust a hidden form
            // field for it.
            $login->username = $username;

            if ($login->validate()) {
                Yii::$app->session->remove(self::SESSION_KEY_STEP1_USERNAME);
                $this->updateRememberUsernameCookie($username, (bool)$login->rememberUsername);
                return $this->onAuthSuccess($login->authClient);
            }
        }

        return $this->renderPasswordStep($login);
    }

    /**
     * Write or remove the "Remember login name" cookie based on the user's
     * Step-2 checkbox choice. The cookie holds only the literal text the user
     * typed on Step 1 (username or email) — no auth credentials.
     */
    private function updateRememberUsernameCookie(string $username, bool $remember): void
    {
        if ($remember && $username !== '') {
            Yii::$app->response->cookies->add(new Cookie([
                'name' => self::COOKIE_REMEMBER_USERNAME,
                'value' => $username,
                'expire' => time() + self::COOKIE_REMEMBER_USERNAME_LIFETIME,
                'httpOnly' => true,
            ]));
        } else {
            Yii::$app->response->cookies->remove(self::COOKIE_REMEMBER_USERNAME);
        }
    }

    /**
     * Render the Step-2 password view. Picks the modal variant for AJAX
     * requests and the page variant otherwise. Used both by {@see actionPassword()}
     * and by {@see actionLogin()} when serving an in-modal Step-1 → Step-2
     * transition (no redirect).
     */
    private function renderPasswordStep(?LoginPassword $login = null)
    {
        if ($login === null) {
            $login = new LoginPassword();
            $login->username = (string)Yii::$app->session->get(self::SESSION_KEY_STEP1_USERNAME, '');
            // Pre-check "Remember login name" when the user already opted in
            // previously, so unchecking it on the next sign-in actually
            // forgets them instead of leaving the cookie behind.
            $cookieUsername = Yii::$app->request->cookies->getValue(self::COOKIE_REMEMBER_USERNAME);
            $login->rememberUsername = is_string($cookieUsername) && $cookieUsername !== '';
        }

        $params = [
            'model' => $login,
            'signUpAllowed' => self::isSelfRegistrationFormVisible(),
            'passwordRecoveryRoute' => $this->module->passwordRecoveryRoute,
        ];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('login_password_modal', $params);
        }

        return $this->render('login_password', $params);
    }

    /**
     * Self-registration entry point — shown when the user clicks "Sign Up" on
     * the login screen. Collects an email (plus captcha) and dispatches the
     * existing {@see Invite::selfInvite()} mail flow. After the email link is
     * clicked, the user lands in {@see RegistrationController::actionIndex()}
     * which handles the actual account creation.
     *
     * @since 1.19
     */
    public function actionRegister()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goBack();
        }

        if (!self::isSelfRegistrationEnabled()) {
            return $this->redirect(['/user/auth/login']);
        }

        $invite = new Invite();
        $invite->scenario = Invite::SCENARIO_INVITE;

        if ($invite->load(Yii::$app->request->post()) && $invite->selfInvite()) {
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('register_success_modal', ['model' => $invite]);
            }
            return $this->render('register_success', ['model' => $invite]);
        }

        $params = ['invite' => $invite];

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('register_modal', $params);
        }

        return $this->render('register', $params);
    }

    /**
     * Handle successful authentication
     *
     * @param BaseClient $authClient
     * @return Response
     * @throws Throwable
     */
    public function onAuthSuccess(BaseClient $authClient)
    {
        // User already logged in - Add new authclient to existing user
        if (!Yii::$app->user->isGuest) {
            if (!$this->isAuthClientAllowed(Yii::$app->user->identity, $authClient)) {
                Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'This login method is not allowed for your account.'));
                return $this->redirect(['/user/account/connected-accounts']);
            }
            Yii::$app->user->getAuthClientUserService()->add($authClient);
            return $this->redirect(['/user/account/connected-accounts']);
        }

        $authClientService = new AuthClientService($authClient);
        $authClientService->autoMapToExistingUser();

        $user = $authClientService->getUser();

        // Maintenance gate — applies to all auth flows (form login, OAuth,
        // SAML, …). Non-admins are bounced before any session is created and
        // land on the maintenance view via the /user/auth/login redirect.
        if (Yii::$app->settings->get('maintenanceMode') && !($user && $user->isSystemAdmin())) {
            Yii::$app->session->setFlash(
                'error',
                Yii::t('UserModule.auth', 'Only administrators can sign in during maintenance mode.'),
            );
            Yii::$app->session->remove(self::SESSION_KEY_STEP1_USERNAME);
            return Yii::$app->request->isAjax
                ? $this->htmlRedirect(['/user/auth/login'])
                : $this->redirect(['/user/auth/login']);
        }

        if ($user !== null) {
            if (!$this->isAuthClientAllowed($user, $authClient)) {
                Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'This login method is not allowed for your account.'));
                return $this->redirect(['/user/auth/login']);
            }
            return $this->login($user, $authClient);
        }

        return $this->register($authClient);
    }


    /**
     * Try to register (automatic user creation or start the registration process) after successful authentication
     * without found related user account
     *
     * @param BaseClient $authClient
     * @return Response|\yii\console\Response|\yii\web\Response
     * @throws HttpException
     * @throws Exception
     */
    private function register(BaseClient $authClient)
    {
        $attributes = $authClient->getUserAttributes();

        // Check if E-Mail is given by the AuthClient
        if (!isset($attributes['email']) && $this->module->emailRequired) {
            Yii::warning('Could not register user automatically: AuthClient ' . $authClient::class . ' provided no E-Mail attribute.', 'user');
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'Missing E-Mail Attribute from AuthClient.'));
            return $this->redirect(['/user/auth/login']);
        }

        // Check that AuthClient provides an ID for the user (mandatory)
        if (!isset($attributes['id'])) {
            Yii::warning('Could not register user automatically: AuthClient ' . $authClient::class . ' provided no ID attribute.', 'user');
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'Missing ID AuthClient Attribute from AuthClient.'));
            return $this->redirect(['/user/auth/login']);
        }

        $authClientService = new AuthClientService($authClient);
        $inviteRegistrationService = InviteRegistrationService::createFromRequestOrEmail($attributes['email'] ?? null);
        $linkRegistrationService = LinkRegistrationService::createFromRequest();

        if (!$inviteRegistrationService->isValid()
            && !$linkRegistrationService->isValid()
            && (!$authClientService->allowSelfRegistration() && !in_array($authClient->id, $this->module->allowUserRegistrationFromAuthClientIds))
        ) {
            Yii::warning('Could not register user automatically: Anonymous registration disabled. AuthClient: ' . $authClient::class, 'user');
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'You\'re not registered.'));
            return $this->redirect(['/user/auth/login']);
        }

        if (!empty($attributes['email']) && $linkRegistrationService->isValid()) {
            $linkRegistrationService->convertToInvite($attributes['email']);
        }

        // Try automatic user creation
        $user = $authClientService->createUser();
        if ($user !== null) {
            return $this->login($user, $authClient);
        }

        // Start Registration
        if ($authClient instanceof SerializableAuthClient) {
            $authClient->beforeSerialize();
        }

        // Store authclient in session - for registration controller
        Yii::$app->session->set('authClient', $authClient);

        return $this->redirect(['/user/registration']);
    }

    /**
     * Do log in user
     *
     * @param User $user
     * @param BaseClient $authClient
     * @param array $redirectUrl
     * @return array
     */
    private function doLogin($user, $authClient, $redirectUrl)
    {
        $duration = 0;

        if ($authClient instanceof BaseFormAuth && $authClient->login->rememberMe) {
            $duration = Yii::$app->getModule('user')->loginRememberMeDuration;
        }

        (new AuthClientService($authClient))->updateUser($user);

        if ($success = Yii::$app->user->login($user, $duration)) {
            Yii::$app->user->setCurrentAuthClient($authClient);
            $redirectUrl = Yii::$app->user->returnUrl;
        }

        return [$success, $redirectUrl];
    }

    private function isAuthClientAllowed(User $user, BaseClient $authClient): bool
    {
        return UserSourceService::getForUser($user)->isAuthClientAllowed($authClient->getId());
    }

    /**
     * Login user
     *
     * @param User $user
     * @param BaseClient $authClient
     * @return Response the current response object
     */
    protected function login($user, $authClient)
    {
        $redirectUrl = ['/user/auth/login'];
        $success = false;
        $this->trigger(static::EVENT_BEFORE_CHECKING_USER_STATUS, new UserEvent(['user' => $user]));

        if ($user->status == User::STATUS_ENABLED) {
            [$success, $redirectUrl] = $this->doLogin($user, $authClient, $redirectUrl);
        } elseif ($user->status == User::STATUS_DISABLED) {
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'Your account is disabled!'));
        } elseif ($user->status == User::STATUS_NEED_APPROVAL) {
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'Your account is not approved yet!'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'Unknown user status!'));
        }

        if ($success) {
            // Add space invite
            $linkRegistrationService = LinkRegistrationService::createFromRequest();
            if (
                $linkRegistrationService->isValid()
                && $linkRegistrationService->inviteToSpace(Yii::$app->user->identity)
            ) {
                $redirectUrl = $linkRegistrationService->getSpace()->getUrl();
            }
        }

        // NOTE: The method `htmlRedirect` renders `Html::nonce()`, so it must be run before
        //       a resetting of nonce on the event `humhub\modules\web\Events\onAfterLogin`
        $result = Yii::$app->request->getIsAjax()
            ? $this->htmlRedirect($redirectUrl)
            : $this->redirect($redirectUrl);

        if ($success) {
            $this->trigger(static::EVENT_AFTER_LOGIN, new UserEvent(['user' => Yii::$app->user->identity]));
            if (method_exists($authClient, 'onSuccessLogin')) {
                $authClient->onSuccessLogin();
            }
        }

        return $result;
    }

    /**
     * Logouts a User
     * @throws HttpException
     */
    public function actionLogout()
    {
        $this->forcePostRequest();

        $language = Yii::$app->user->language;

        Yii::$app->user->logout();

        // Store users language in session
        if ($language !== '') {
            $cookie = new Cookie([
                'name' => 'language',
                'value' => $language,
                'expire' => time() + 86400 * 365,
            ]);
            Yii::$app->getResponse()->getCookies()->add($cookie);
        }

        return $this->redirect($this->module->logoutUrl ?: Yii::$app->homeUrl);
    }

    /**
     * Allows third party applications
     * to convert a valid sessionId
     * into a username.
     */
    public function actionGetSessionUserJson()
    {
        Yii::$app->response->format = 'json';

        $sessionId = Yii::$app->request->get('sessionId');

        $output = [];
        $output['valid'] = false;
        $httpSession = Session::findOne(['id' => $sessionId]);
        if ($httpSession != null && $httpSession->user != null) {
            $output['valid'] = true;
            $output['userName'] = $httpSession->user->username;
            $output['fullName'] = $httpSession->user->displayName;
            $output['email'] = $httpSession->user->email;
            $output['superadmin'] = $httpSession->user->isSystemAdmin();
        }

        return $output;
    }

    /**
     * Sign in back to admin User who impersonated the current User
     *
     * @return \yii\console\Response|\yii\web\Response
     * @throws HttpException
     */
    public function actionStopImpersonation()
    {
        $this->forcePostRequest();

        if (Yii::$app->user->restoreImpersonator()) {
            return $this->redirect(['/admin/user/list']);
        }

        return $this->goBack();
    }
}
