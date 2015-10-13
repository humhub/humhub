<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * AuthController handles all authentication tasks.
 *
 * @package humhub.modules_core.user.controllers
 * @since 0.5
 */
class AuthController extends Controller
{

    //public $layout = '//layouts/main1';
    public $layout = "application.modules_core.user.views.layouts.main_auth";
    public $subLayout = "_layout";

    public function actions()
    {
        return array(
            // captcha action renders the CAPTCHA image displayed on the password recovery page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
            ),
        );
    }

    /**
     * Displays the login page
     */
    public function actionLogin()
    {

        // If user is already logged in, redirect him to the dashboard
        if (!Yii::app()->user->isGuest) {
            $this->redirect(Yii::app()->user->returnUrl);
        }

        // Show/Allow Anonymous Registration
        $canRegister = HSetting::Get('anonymousRegistration', 'authentication_internal');
        $model = new AccountLoginForm;

        //TODO: Solve this via events!
        if (Yii::app()->getModule('zsso') != null) {
            ZSsoModule::beforeActionLogin();
        }

        // if it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'account-login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if (isset($_POST['AccountLoginForm'])) {
            $model->attributes = $_POST['AccountLoginForm'];

            // validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login()) {
                $user = User::model()->findByPk(Yii::app()->user->id);

                if (Yii::app()->request->isAjaxRequest) {
                    $this->htmlRedirect(Yii::app()->user->returnUrl);
                } else {
                    $this->redirect(Yii::app()->user->returnUrl);
                }
            }
        }

        // Always clear password
        $model->password = "";

        $registerModel = new AccountRegisterForm;

        // Registration enabled?
        if ($canRegister) {

            // if it is ajax validation request
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'account-register-form') {
                echo CActiveForm::validate($registerModel);
                Yii::app()->end();
            }

            if (isset($_POST['AccountRegisterForm'])) {
                $registerModel->attributes = $_POST['AccountRegisterForm'];

                if ($registerModel->validate()) {

                    // Try Load an invite
                    $userInvite = UserInvite::model()->findByAttributes(array('email' => $registerModel->email));

                    if ($userInvite === null) {
                        $userInvite = new UserInvite();
                    }

                    $userInvite->email = $registerModel->email;
                    $userInvite->source = UserInvite::SOURCE_SELF;
                    $userInvite->language = Yii::app()->language;
                    $userInvite->save();

                    $userInvite->sendInviteMail();

                    $this->render('register_success', array(
                        'model' => $registerModel,
                    ));
                    return;
                }
            }
        }

        if (Yii::app()->request->isAjaxRequest) {
            $this->renderPartial('login_modal', array('model' => $model, 'registerModel' => $registerModel, 'canRegister' => $canRegister), false, true);
        } else {
            $this->render('login', array('model' => $model, 'registerModel' => $registerModel, 'canRegister' => $canRegister));
        }
    }

    /**
     * Recover Password Action
     * Generates an password reset token and sends an e-mail to the user.
     */
    public function actionRecoverPassword()
    {
        $model = new AccountRecoverPasswordForm;

        if (isset($_POST['AccountRecoverPasswordForm'])) {
            $model->attributes = $_POST['AccountRecoverPasswordForm'];

            if ($model->validate()) {

                // Force new Captcha Code
                Yii::app()->getController()->createAction('captcha')->getVerifyCode(true);

                $model->recoverPassword();

                if (Yii::app()->request->isAjaxRequest) {
                    $this->renderPartial('recoverPassword_modal_success', array('model' => $model), false, true);
                } else {
                    $this->render('recoverPassword_success', array(
                        'model' => $model,
                    ));
                }
                return;
            }
        }

        if (Yii::app()->request->isAjaxRequest) {
            $this->renderPartial('recoverPassword_modal', array('model' => $model), false, true);
        } else {
            $this->render('recoverPassword', array(
                'model' => $model,
            ));
        }
    }

    /**
     * Resets users password based on given token
     */
    public function actionResetPassword()
    {

        $user = User::model()->findByAttributes(array('guid' => Yii::app()->request->getQuery('guid')));

        if ($user === null || !$this->checkPasswordResetToken($user, Yii::app()->request->getQuery('token'))) {
            throw new CHttpException('500', 'It looks like you clicked on an invalid password reset link. Please try again.');
        }

        $model = new UserPassword('newPassword');

        if (isset($_POST['UserPassword'])) {
            $model->attributes = $_POST['UserPassword'];

            if ($model->validate()) {

                // Clear password reset token
                $user->setSetting('passwordRecoveryToken', '', 'user');

                $model->user_id = $user->id;
                $model->setPassword($model->newPassword);
                $model->save();

                return $this->render('resetPassword_success');
            }
        }

        $this->render('resetPassword', array(
            'model' => $model,
        ));
    }

    private function checkPasswordResetToken($user, $token)
    {
        // Saved token - Format: randomToken.generationTime
        $savedTokenInfo = $user->getSetting('passwordRecoveryToken', 'user');

        if ($savedTokenInfo !== "") {
            list($generatedToken, $generationTime) = explode('.', $savedTokenInfo);
            if (CPasswordHelper::same($generatedToken, $token)) {
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

        $_POST = Yii::app()->input->stripClean($_POST);

        $needApproval = HSetting::Get('needApproval', 'authentication_internal');

        if (!Yii::app()->user->isGuest)
            throw new CHttpException(401, 'Your are already logged in! - Logout first!');

        // Check for valid user invite
        $userInvite = UserInvite::model()->findByAttributes(array('token' => Yii::app()->request->getQuery('token')));
        if (!$userInvite)
            throw new CHttpException(404, 'Token not found!');

        if ($userInvite->language)
            Yii::app()->setLanguage($userInvite->language);

        $userModel = new User('register');
        $userModel->email = $userInvite->email;
        $userPasswordModel = new UserPassword('newPassword');
        $profileModel = $userModel->profile;
        $profileModel->scenario = 'register';

        // Build Form Definition
        $definition = array();
        $definition['elements'] = array();

        $groupModels = Group::model()->findAll(array('order' => 'name'));

        $defaultUserGroup = HSetting::Get('defaultUserGroup', 'authentication_internal');
        $groupFieldType = "dropdownlist";
        if ($defaultUserGroup != "") {
            $groupFieldType = "hidden";
        } else if (count($groupModels) == 1) {
            $groupFieldType = "hidden";
            $defaultUserGroup = $groupModels[0]->id;
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
                    'items' => CHtml::listData($groupModels, 'id', 'name'),
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
        $form['User']->model = $userModel;
        $form['UserPassword']->model = $userPasswordModel;
        $form['Profile']->model = $profileModel;

        if ($form->submitted('save') && $form->validate()) {

            $this->forcePostRequest();

            // Registe User
            $form['User']->model->email = $userInvite->email;
            $form['User']->model->language = Yii::app()->getLanguage();
            if ($form['User']->model->save()) {

                // Save User Profile
                $form['Profile']->model->user_id = $form['User']->model->id;
                $form['Profile']->model->save();

                // Save User Password
                $form['UserPassword']->model->user_id = $form['User']->model->id;
                $form['UserPassword']->model->setPassword($form['UserPassword']->model->newPassword);
                $form['UserPassword']->model->save();

                // Autologin user
                if (!$needApproval) {
                    $user = $form['User']->model;
                    $newIdentity = new UserIdentity($user->username, '');
                    $newIdentity->fakeAuthenticate();
                    Yii::app()->user->login($newIdentity);
                    $this->redirect(array('//dashboard/dashboard'));
                    return;
                }

                $this->render('createAccount_success', array(
                    'form' => $form,
                    'needApproval' => $needApproval,
                ));

                return;
            }
        }

        $this->render('createAccount', array(
            'form' => $form,
            'needAproval' => $needApproval)
        );
    }

    /**
     * Logouts a User
     *
     */
    public function actionLogout()
    {
        $language = Yii::app()->user->language;
        
        Yii::app()->user->logout();

        // Store users language in session
        if ($language != "") {
            Yii::app()->request->cookies['language'] = new CHttpCookie('language', $language);
        }
 
        $this->redirect(Yii::app()->homeUrl);
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

        if (!Yii::app()->user->isGuest) {
            $out['loggedIn'] = true;
        }

        print CJSON::encode($out);
        Yii::app()->end();
    }

    /**
     * Allows third party applications to convert a valid sessionId
     * into a username.
     */
    public function actionGetSessionUserJson()
    {

        $sessionId = Yii::app()->request->getQuery('sessionId');

        $output = array();
        $output['valid'] = false;
        $httpSession = UserHttpSession::model()->with('user')->findByAttributes(array('id' => $sessionId));
        if ($httpSession != null && $httpSession->user != null) {
            $output['valid'] = true;
            $output['userName'] = $httpSession->user->username;
            $output['fullName'] = $httpSession->user->displayName;
            $output['email'] = $httpSession->user->email;
            $output['superadmin'] = $httpSession->user->super_admin;
        }

        print CJSON::encode($output);
        Yii::app()->end();
    }

}

?>
