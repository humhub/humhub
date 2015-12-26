<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use Yii;
use humhub\components\Controller;

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
        ];
    }

    /**
     * Displays the login page
     */
    public function actionLogin()
    {

        // If user is already logged in, redirect him to the dashboard
        if (!Yii::$app->user->isGuest) {
            $this->redirect(Yii::$app->user->returnUrl);
        }

        // Show/Allow Anonymous Registration
        $loginModel = new \humhub\modules\user\models\forms\AccountLogin;
        if ($loginModel->load(Yii::$app->request->post()) && $loginModel->login()) {
            if (Yii::$app->request->getIsAjax()) {
                return $this->htmlRedirect(Yii::$app->user->returnUrl);
            } else {
                return $this->redirect(Yii::$app->user->returnUrl);
            }
        }
        $loginModel->password = "";

        $canRegister = \humhub\models\Setting::Get('anonymousRegistration', 'authentication_internal');
        $registerModel = new \humhub\modules\user\models\forms\AccountRegister;

        if ($canRegister) {
            if ($registerModel->load(Yii::$app->request->post()) && $registerModel->validate()) {

                $invite = \humhub\modules\user\models\Invite::findOne(['email' => $registerModel->email]);
                if ($invite === null) {
                    $invite = new \humhub\modules\user\models\Invite();
                }
                $invite->email = $registerModel->email;
                $invite->source = \humhub\modules\user\models\Invite::SOURCE_SELF;
                $invite->language = Yii::$app->language;
                $invite->save();
                $invite->sendInviteMail();

                if (Yii::$app->request->getIsAjax()) {
                    return $this->render('register_success_modal', ['model' => $registerModel]);
                } else {
                    return $this->render('register_success', ['model' => $registerModel]);
                }
            }
        }

        if (Yii::$app->request->getIsAjax()) {
            return $this->renderAjax('login_modal', array('model' => $loginModel, 'registerModel' => $registerModel, 'canRegister' => $canRegister));
        } else {
            return $this->render('login', array('model' => $loginModel, 'registerModel' => $registerModel, 'canRegister' => $canRegister));
        }
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

        $this->redirect(Yii::$app->homeUrl);
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
            $output['superadmin'] = $httpSession->user->super_admin;
        }
        return $output;
    }

}

?>
