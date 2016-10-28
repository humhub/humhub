<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\user\models\User;
use humhub\modules\user\authclient\AuthAction;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\forms\Login;
use humhub\modules\user\authclient\AuthClientHelpers;
use humhub\modules\user\authclient\interfaces\ApprovalBypass;

/**
 * AuthController handles login and logout
 *
 * @since 0.5
 */
class AuthController extends Controller
{

    /**
     * @inheritdoc
     */
    public $layout = "@humhub/modules/user/views/layouts/main";

    /**
     * @inheritdoc
     */
    public $subLayout = "_layout";

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
            ],
            'external' => [
                'class' => AuthAction::className(),
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
            if (Yii::$app->request->getIsAjax()) {
                return $this->render('register_success_modal', ['model' => $invite]);
            } else {
                return $this->render('register_success', ['model' => $invite]);
            }
        }

        if (Yii::$app->request->getIsAjax()) {
            return $this->renderAjax('login_modal', array('model' => $login, 'invite' => $invite, 'canRegister' => $invite->allowSelfInvite()));
        }
        return $this->render('login', array('model' => $login, 'invite' => $invite, 'canRegister' => $invite->allowSelfInvite()));
    }

    /**
     * Handle successful authentication
     * 
     * @param \yii\authclient\BaseClient $authClient
     * @return Response
     */
    public function onAuthSuccess(\yii\authclient\BaseClient $authClient)
    {
        $attributes = $authClient->getUserAttributes();

        // User already logged in - Add new authclient to existing user
        if (!Yii::$app->user->isGuest) {
            AuthClientHelpers::storeAuthClientForUser($authClient, Yii::$app->user->getIdentity());
            return $this->redirect(['/user/account/connected-accounts']);
        }

        // Login existing user 
        $user = AuthClientHelpers::getUserByAuthClient($authClient);
        if ($user !== null) {
            return $this->login($user, $authClient);
        }

        if (!$authClient instanceof ApprovalBypass && !Yii::$app->getModule('user')->settings->get('auth.anonymousRegistration')) {
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', "You're not registered."));
            return $this->redirect(['/user/auth/login']);
        }

        // Check if E-Mail is given
        if (!isset($attributes['email'])) {
            Yii::$app->session->setFlash('error', "Missing E-Mail Attribute from AuthClient.");
            return $this->redirect(['/user/auth/login']);
        }

        if (!isset($attributes['id'])) {
            Yii::$app->session->setFlash('error', "Missing ID AuthClient Attribute from AuthClient.");
            return $this->redirect(['/user/auth/login']);
        }

        // Check if e-mail is already taken
        if (User::findOne(['email' => $attributes['email']]) !== null) {
            Yii::$app->session->setFlash('error', Yii::t('UserModule.base', 'User with the same email already exists but isn\'t linked to you. Login using your email first to link it.'));
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
     * @param \yii\authclient\BaseClient $authClient
     * @return Response the current response object
     */
    protected function login($user, $authClient)
    {
        $redirectUrl = ['/user/auth/login'];
        if ($user->status == User::STATUS_ENABLED) {
            $duration = 0;
            if ($authClient instanceof \humhub\modules\user\authclient\BaseFormAuth) {
                if ($authClient->login->rememberMe) {
                    $duration = Yii::$app->getModule('user')->loginRememberMeDuration;
                }
            }
            AuthClientHelpers::updateUser($authClient, $user);

            if (Yii::$app->user->login($user, $duration)) {
                Yii::$app->user->setCurrentAuthClient($authClient);
                $url = Yii::$app->user->returnUrl;
            }
        } elseif ($user->status == User::STATUS_DISABLED) {
            Yii::$app->session->setFlash('error', 'Your account is disabled!');
        } elseif ($user->status == User::STATUS_NEED_APPROVAL) {
            Yii::$app->session->setFlash('error', 'Your account is not approved yet!');
        } else {
            Yii::$app->session->setFlash('error', 'Unknown user status!');
        }

        if (Yii::$app->request->getIsAjax()) {
            return $this->htmlRedirect($redirectUrl);
        }

        return $this->redirect($redirectUrl);
    }

    /**
     * Logouts a User
     */
    public function actionLogout()
    {
        $language = Yii::$app->user->language;

        Yii::$app->user->logout();

        // Store users language in session
        if ($language != "") {
            $cookie = new \yii\web\Cookie([
                'name' => 'language',
                'value' => $language,
                'expire' => time() + 86400 * 365,
            ]);
            Yii::$app->getResponse()->getCookies()->add($cookie);
        }

        return $this->redirect(($this->module->logoutUrl) ? $this->module->logoutUrl : Yii::$app->homeUrl);
    }

    /**
     * Allows third party applications to convert a valid sessionId
     * into a username.
     */
    public function actionGetSessionUserJson()
    {
        Yii::$app->response->format = 'json';

        $sessionId = Yii::$app->request->get('sessionId');

        $output = array();
        $output['valid'] = false;
        $httpSession = \humhub\modules\user\models\Session::findOne(['id' => $sessionId]);
        if ($httpSession != null && $httpSession->user != null) {
            $output['valid'] = true;
            $output['userName'] = $httpSession->user->username;
            $output['fullName'] = $httpSession->user->displayName;
            $output['email'] = $httpSession->user->email;
            $output['superadmin'] = $httpSession->user->isSystemAdmin();
        }
        return $output;
    }

}

?>
