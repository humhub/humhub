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
use humhub\modules\user\models\User;
use humhub\modules\user\authclient\AuthAction;
use humhub\modules\user\events\UserEvent;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\forms\Login;
use humhub\modules\user\authclient\AuthClientHelpers;
use humhub\modules\user\authclient\interfaces\ApprovalBypass;
use humhub\modules\user\authclient\BaseFormAuth;
use humhub\modules\user\models\Session;
use Yii;
use yii\web\Cookie;
use yii\authclient\BaseClient;

/**
 * AuthController handles login and logout
 *
 * @since 0.5
 */
class AuthController extends Controller
{
    /**
     * @event Triggered after an successful login. Note: In contrast to User::EVENT_AFTER_LOGIN, this event is triggered
     * after the response is generated.
     */
    const EVENT_AFTER_LOGIN = 'afterLogin';
    
    /**
     * @event Triggered after an successful login but before checking user status
     */
    const EVENT_BEFORE_CHECKING_USER_STATUS = 'beforeCheckingUserStatus';

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
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'external' => [
                'class' => AuthAction::class,
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        // Remove authClient from session - if already exists
        Yii::$app->session->remove('authClient');

        return parent::beforeAction($action);
    }

    /**
     * Displays the login page
     */
    public function actionLogin()
    {
        // If user is already logged in, redirect him to the dashboard
        if (!Yii::$app->user->isGuest) {
            return $this->goBack();
        }

        // Login Form Handling
        $login = new Login;
        if ($login->load(Yii::$app->request->post()) && $login->validate()) {
            return $this->onAuthSuccess($login->authClient);
        }

        // Self Invite
        $invite = new Invite();
        $invite->scenario = 'invite';
        if ($invite->load(Yii::$app->request->post()) && $invite->selfInvite()) {
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('register_success_modal', ['model' => $invite]);
            } else {
                return $this->render('register_success', ['model' => $invite]);
            }
        }

        $loginParams = [
            'model' => $login,
            'invite' => $invite,
            'canRegister' => $invite->allowSelfInvite(),
        ];

        if (Yii::$app->settings->get('maintenanceMode')) {
            Yii::$app->session->setFlash('error', ControllerAccess::getMaintenanceModeWarningText());
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('login_modal', $loginParams);
        }

        return $this->render('login', $loginParams);
    }

    /**
     * Handle successful authentication
     *
     * @param BaseClient $authClient
     * @return Response
     * @throws \Throwable
     */
    public function onAuthSuccess(BaseClient $authClient)
    {
        $attributes = $authClient->getUserAttributes();

        // User already logged in - Add new authclient to existing user
        if (!Yii::$app->user->isGuest) {
            AuthClientHelpers::storeAuthClientForUser($authClient, Yii::$app->user->getIdentity());
            return $this->redirect(['/user/account/connected-accounts']);
        }

        $user = AuthClientHelpers::getUserByAuthClient($authClient);

        if (Yii::$app->settings->get('maintenanceMode') && !$user->isSystemAdmin()) {
            return $this->redirect(['/user/auth/login']);
        }

        // Check if e-mail is already in use with another auth method
        if ($user === null && isset($attributes['email'])) {
            $user = User::findOne(['email' => $attributes['email']]);
            if ($user !== null) {
                // Map current auth method to user with same e-mail address
                AuthClientHelpers::storeAuthClientForUser($authClient, $user);
            }
        }

        if ($user !== null) {
            return $this->login($user, $authClient);
        }

        if (!$authClient instanceof ApprovalBypass && !Yii::$app->getModule('user')->settings->get('auth.anonymousRegistration')) {
            Yii::warning('Could not register user automatically: Anonymous registration disabled. AuthClient: ' . get_class($authClient), 'user');
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', "You're not registered."));
            return $this->redirect(['/user/auth/login']);
        }

        // Check if E-Mail is given
        if (!isset($attributes['email']) && Yii::$app->getModule('user')->emailRequired) {
            Yii::warning('Could not register user automatically: AuthClient ' . get_class($authClient) . ' provided no E-Mail attribute.', 'user');
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'Missing E-Mail Attribute from AuthClient.'));
            return $this->redirect(['/user/auth/login']);
        }

        if (!isset($attributes['id'])) {
            Yii::warning('Could not register user automatically: AuthClient ' . get_class($authClient) . ' provided no ID attribute.', 'user');
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'Missing ID AuthClient Attribute from AuthClient.'));
            return $this->redirect(['/user/auth/login']);
        }

        // Try automatically create user & login user
        $user = AuthClientHelpers::createUser($authClient);
        if ($user !== null) {
            return $this->login($user, $authClient);
        }

        // Make sure we normalized user attributes before put it in session (anonymous functions)
        $authClient->setNormalizeUserAttributeMap([]);

        // Store authclient in session - for registration controller
        Yii::$app->session->set('authClient', $authClient);

        // Start registration process
        return $this->redirect(['/user/registration']);
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
            $duration = 0;
            if (
                ($authClient instanceof BaseFormAuth && $authClient->login->rememberMe) ||
                !empty(Yii::$app->session->get('loginRememberMe'))) {

                $duration = Yii::$app->getModule('user')->loginRememberMeDuration;
            }
            AuthClientHelpers::updateUser($authClient, $user);

            if ($success = Yii::$app->user->login($user, $duration)) {
                Yii::$app->user->setCurrentAuthClient($authClient);
                $redirectUrl = Yii::$app->user->returnUrl;
            }
        } elseif ($user->status == User::STATUS_DISABLED) {
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'Your account is disabled!'));
        } elseif ($user->status == User::STATUS_NEED_APPROVAL) {
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'Your account is not approved yet!'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'Unknown user status!'));
        }

        $result = Yii::$app->request->getIsAjax() ? $this->htmlRedirect($redirectUrl) : $this->redirect($redirectUrl);

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
     * @throws \yii\web\HttpException
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

        return $this->redirect(($this->module->logoutUrl) ? $this->module->logoutUrl : Yii::$app->homeUrl);
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
