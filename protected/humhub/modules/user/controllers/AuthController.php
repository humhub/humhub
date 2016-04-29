<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use Yii;
use yii\web\HttpException;
use yii\helpers\Url;
use humhub\components\Controller;
use humhub\modules\user\models\Invite;
use humhub\compat\HForm;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Password;
use humhub\modules\user\models\forms\AccountRecoverPassword;

/**
 * AuthController handles all authentication tasks.
 *
 * @package humhub.modules_core.user.controllers
 * @since 0.5
 */
class AuthController extends Controller
{

    //public $layout = '//layouts/main1';
    public $layout = "@humhub/modules/user/views/layouts/main";
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
            return $this->redirect(Yii::$app->user->returnUrl);
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
     * Recover Password Action
     * Generates an password reset token and sends an e-mail to the user.
     */
    public function actionRecoverPassword()
    {
        $model = new AccountRecoverPassword();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->recover()) {
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('recoverPassword_modal_success', array('model' => $model));
            }
            return $this->render('recoverPassword_success', array('model' => $model));
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('recoverPassword_modal', array('model' => $model));
        }
        return $this->render('recoverPassword', array('model' => $model));
    }

    /**
     * Resets users password based on given token
     */
    public function actionResetPassword()
    {

        $user = User::findOne(array('guid' => Yii::$app->request->get('guid')));

        if ($user === null || !$this->checkPasswordResetToken($user, Yii::$app->request->get('token'))) {
            throw new HttpException('500', 'It looks like you clicked on an invalid password reset link. Please try again.');
        }

        $model = new Password();
        $model->scenario = 'registration';

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user->setSetting('passwordRecoveryToken', '', 'user');
            $model->user_id = $user->id;
            $model->setPassword($model->newPassword);
            $model->save();
            return $this->render('resetPassword_success');
        }

        return $this->render('resetPassword', array('model' => $model));
    }

    private function checkPasswordResetToken($user, $token)
    {
        // Saved token - Format: randomToken.generationTime
        $savedTokenInfo = $user->getSetting('passwordRecoveryToken', 'user');

        if ($savedTokenInfo !== "") {
            list($generatedToken, $generationTime) = explode('.', $savedTokenInfo);
            if (\humhub\libs\Helpers::same($generatedToken, $token)) {
                // Check token generation time
                if ($generationTime + (24 * 60 * 60) >= time()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Create an account
     *
     * This action is called after e-mail validation.
     */
    public function actionCreateAccount()
    {

        $needApproval = \humhub\models\Setting::Get('needApproval', 'authentication_internal');

        if (!Yii::$app->user->isGuest)
            throw new HttpException(401, 'Your are already logged in! - Logout first!');


        $userInvite = Invite::findOne(['token' => Yii::$app->request->get('token')]);
        if (!$userInvite)
            throw new HttpException(404, 'Token not found!');

        if ($userInvite->language)
            Yii::$app->language = $userInvite->language;

        $userModel = new User();
        $userModel->scenario = 'registration';
        $userModel->email = $userInvite->email;

        $userPasswordModel = new Password();
        $userPasswordModel->scenario = 'registration';

        $profileModel = $userModel->profile;
        $profileModel->scenario = 'registration';

        // Build Form Definition
        $definition = array();
        $definition['elements'] = array();


        $groupModels = \humhub\modules\user\models\Group::find()->orderBy('name ASC')->all();
        $defaultUserGroup = \humhub\models\Setting::Get('defaultUserGroup', 'authentication_internal');
        $groupFieldType = "dropdownlist";
        if ($defaultUserGroup != "") {
            $groupFieldType = "hidden";
        } else if (count($groupModels) == 1) {
            $groupFieldType = "hidden";
            $defaultUserGroup = $groupModels[0]->id;
        }
        if ($groupFieldType == 'hidden') {
            $userModel->group_id = $defaultUserGroup;
        }

        // Add User Form
        $definition['elements']['User'] = array(
            'type' => 'form',
            'title' => Yii::t('UserModule.controllers_AuthController', 'Account'),
            'elements' => array(
                'username' => array(
                    'type' => 'text',
                    'class' => 'form-control',
                    'maxlength' => 25,
                ),
                'group_id' => array(
                    'type' => $groupFieldType,
                    'class' => 'form-control',
                    'items' => \yii\helpers\ArrayHelper::map($groupModels, 'id', 'name'),
                    'value' => $defaultUserGroup,
                ),
            ),
        );

        // Add User Password Form
        $definition['elements']['UserPassword'] = array(
            'type' => 'form',
            #'title' => 'Password',
            'elements' => array(
                'newPassword' => array(
                    'type' => 'password',
                    'class' => 'form-control',
                    'maxlength' => 255,
                ),
                'newPasswordConfirm' => array(
                    'type' => 'password',
                    'class' => 'form-control',
                    'maxlength' => 255,
                ),
            ),
        );

        // Add Profile Form
        $definition['elements']['Profile'] = array_merge(array('type' => 'form'), $profileModel->getFormDefinition());

        // Get Form Definition
        $definition['buttons'] = array(
            'save' => array(
                'type' => 'submit',
                'class' => 'btn btn-primary',
                'label' => Yii::t('UserModule.controllers_AuthController', 'Create account'),
            ),
        );

        $form = new HForm($definition);
        $form->models['User'] = $userModel;
        $form->models['UserPassword'] = $userPasswordModel;
        $form->models['Profile'] = $profileModel;

        if ($form->submitted('save') && $form->validate()) {

            $this->forcePostRequest();

            // Registe User
            $form->models['User']->email = $userInvite->email;
            $form->models['User']->language = Yii::$app->language;
            if ($form->models['User']->save()) {

                // Save User Profile
                $form->models['Profile']->user_id = $form->models['User']->id;
                $form->models['Profile']->save();

                // Save User Password
                $form->models['UserPassword']->user_id = $form->models['User']->id;
                $form->models['UserPassword']->setPassword($form->models['UserPassword']->newPassword);
                $form->models['UserPassword']->save();

                // Autologin user
                if (!$needApproval) {
                    Yii::$app->user->switchIdentity($form->models['User']);
                    return $this->redirect(Url::to(['/dashboard/dashboard']));
                }

                return $this->render('createAccount_success', array(
                            'form' => $form,
                            'needApproval' => $needApproval,
                ));
            }
        }

        return $this->render('createAccount', array(
                    'hForm' => $form,
                    'needAproval' => $needApproval)
        );
    }

    /**
     * Logouts a User
     *
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

        return $this->redirect(Yii::$app->homeUrl);
    }

    /**
     * Check Login State
     *
     * Generates a JSON Output including the current session state.
     * (Whether the user is logged in or not)
     *
     * Can also used as a kind of keep alive.
     */
    public function actionCheckSessionState()
    {
        $out = array();
        $out['loggedIn'] = false;

        if (!Yii::$app->user->isGuest) {
            $out['loggedIn'] = true;
        }

        print CJSON::encode($out);
        Yii::$app->end();
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
