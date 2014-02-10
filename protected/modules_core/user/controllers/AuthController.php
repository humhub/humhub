<?php

/**
 * AuthController handles all authentication tasks.
 *
 * @author Luke
 * @package humhub.modules_core.user.controllers
 * @since 0.5
 */
class AuthController extends Controller {

    /**
     * Displays the login page
     */
    public function actionLogin() {

        // If user is already logged in, redirect him to the dashboard
        if (!Yii::app()->user->isGuest) {
            $this->redirect(Yii::app()->user->returnUrl);
        }

        // Show/Allow Anonymous Registration
        $canRegister = HSetting::Get('anonymousRegistration', 'authentication_internal');


        $ntlmAutoLogin = false;

        // Disable Sublayout
        $this->subLayout = "";

        $model = new AccountLoginForm;

        //TODO: Solve this via events!
        if (Yii::app()->moduleManager->isEnabled('zsso')) {
            ZSsoModule::beforeActionLogin();
        }

        // if it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'account-login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if (isset($_POST['AccountLoginForm'])) {

            $_POST['AccountLoginForm'] = Yii::app()->input->stripClean($_POST['AccountLoginForm']);
            $model->attributes = $_POST['AccountLoginForm'];

            // validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login())
                $this->redirect(Yii::app()->user->returnUrl);
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
                $_POST['AccountRegisterForm'] = Yii::app()->input->stripClean($_POST['AccountRegisterForm']);

                $registerModel->attributes = $_POST['AccountRegisterForm'];

                // Dont allow LDAP Registration
                if (!HAccount::HasAuthMode('local')) {
                    throw new CHttpException(500, Yii::t('UserModule.base', 'User registration not allowed!'));
                }

                if ($registerModel->validate()) {

                    // Try Load an invite
                    $userInvite = UserInvite::model()->findByAttributes(array('email' => $registerModel->email));

                    if (!$userInvite)
                        $userInvite = new UserInvite();

                    $userInvite->email = $registerModel->email;
                    $userInvite->source = UserInvite::SOURCE_SELF;
                    $userInvite->save();

                    $userInvite->sendInviteMail();

                    $this->render('register_success', array(
                        'model' => $registerModel,
                    ));
                    return;
                }
            }
        }


        // display the login form
        $this->render('login', array('model' => $model, 'registerModel' => $registerModel, 'canRegister' => $canRegister));
    }

    /**
     * Recover Password Action
     *
     * @todo check local auth_mode
     */
    public function actionRecoverPassword() {

        // Disable Sublayout
        $this->subLayout = "";

        // Password Recovery only possible with enabled local auth
        if (!HAccount::HasAuthMode('local')) {
            throw new CHttpException(500, 'Forgot Password only possible with "local" AuthMode!');
        }

        $model = new AccountRecoverPasswordForm;

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['AccountRecoverPasswordForm'])) {
            $_POST['AccountRecoverPasswordForm'] = Yii::app()->input->stripClean($_POST['AccountRecoverPasswordForm']);
            $model->attributes = $_POST['AccountRecoverPasswordForm'];

            if ($model->validate()) {

                $model->recoverPassword();

                $this->render('recoverPassword_success', array(
                    'model' => $model,
                ));
                return;
            }
        }

        $this->render('recoverPassword', array(
            'model' => $model,
        ));
    }

    /**
     * Create Account Action
     *
     * This action is called after e-mail validation.
     *
     */
    public function actionCreateAccount() {

        $_POST = Yii::app()->input->stripClean($_POST);

        // Disable Sublayout
        $this->subLayout = "";

        $needApproval = HSetting::Get('needApproval', 'authentication_internal');

        if (!Yii::app()->user->isGuest)
            throw new CHttpException(401, 'Your are already logged in! - Logout first!');

        if (!HAccount::HasAuthMode('local'))
            throw new CHttpException(500, 'Account creation only supported on local auth!');

        // Check for valid user invite
        $userInvite = UserInvite::model()->findByAttributes(array('token' => Yii::app()->request->getQuery('token')));
        if (!$userInvite)
            throw new CHttpException(404, 'Token not found!');

        $userModel = new User('register');
        $userModel->email = $userInvite->email;
        $profileModel = $userModel->profile;
        $profileModel->scenario = 'register';

        // Build Form Definition
        $definition = array();
        $definition['elements'] = array();

        $groupModels = Group::model()->findAll(array('order' => 'name'));

        // Add User Form
        $definition['elements']['User'] = array(
            'type' => 'form',
            'title' => 'Account',
            'elements' => array(
                'username' => array(
                    'type' => 'text',
                    'class' => 'form-control',
                    'maxlength' => 32,
                ),
                'password' => array(
                    'type' => 'password',
                    'class' => 'form-control',
                    'maxlength' => 32,
                ),
                'passwordVerify' => array(
                    'type' => 'password',
                    'class' => 'form-control',
                    'maxlength' => 32,
                ),
                'group_id' => array(
                    'type' => 'dropdownlist',
                    'class' => 'form-control',
                    'items' => CHtml::listData($groupModels, 'id', 'name'),
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
                'label' => Yii::t('base', 'Create account'),
            ),
        );

        $form = new HForm($definition);
        $form['User']->model = $userModel;
        $form['Profile']->model = $profileModel;

        if ($form->submitted('save') && $form->validate()) {
            $this->forcePostRequest();

            if ($form['User']->model->register($userInvite)) {
                $form['Profile']->model->user_id = $form['User']->model->id;
                $form['Profile']->model->save();

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
    public function actionLogout() {

        Yii::app()->user->logout();

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
    public function actionCheckSessionState() {
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
    public function actionGetSessionUserJson() {

        $sessionId = Yii::app()->request->getQuery('sessionId');

        $output = array();
        $output['valid'] = false;
        $httpSession = UserHttpSession::model()->with('user')->findByAttributes(array('id' => $sessionId));
        if ($httpSession != null && $httpSession->user != null) {
            $output['valid'] = true;
            $output['userName'] = $httpSession->user->username;
            $output['fullName'] = $httpSession->user->displayName;
            $output['email'] = $httpSession->user->email;
        }

        print CJSON::encode($output);
        Yii::app()->end();
    }

}

?>
