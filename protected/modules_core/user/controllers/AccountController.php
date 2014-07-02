<?php

/**
 * AccountController provides all standard actions for the current logged in
 * user account.
 *
 * @author Luke
 * @package humhub.modules_core.user.controllers
 * @since 0.5
 */
class AccountController extends Controller
{

    public $subLayout = "_layout";

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {

        // Only allow authenticated users
        return array(
            array('allow',
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Change Account
     *
     * @todo Add Group
     */
    public function actionEditSettings()
    {

        // Load current user model in edit mode
        $model = User::model()->findByPk(Yii::app()->user->id);
        $model->scenario = 'edit';

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-editAccount-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['User'])) {

            $_POST['User'] = Yii::app()->input->stripClean($_POST['User']);
            $model->attributes = $_POST['User'];

            if ($model->validate()) {

                // Create User
                $model->save();

                // Reload User in Session
                Yii::app()->user->reload();

                // set flash message
                Yii::app()->user->setFlash('data-saved', Yii::t('base', 'Saved'));

                $this->refresh();

                // form inputs are valid, do something here
                return;
            }
        }

        $this->render('editSettings', array('model' => $model));
    }

    /**
     * Allows the user to enable user specifc modules
     */
    public function actionEditModules()
    {
        $user = Yii::app()->user->getModel();
        $this->render('editModules', array('user' => $user, 'availableModules' => $user->getAvailableModules()));
    }

    public function actionEnableModule()
    {
        $user = Yii::app()->user->getModel();
        $moduleId = Yii::app()->request->getParam('moduleId', "");

        if (!$user->isModuleEnabled($moduleId)) {
            $user->installModule($moduleId);
        }

        $this->redirect($this->createUrl('//user/account/editModules', array()));
    }

    public function actionDisableModule()
    {

        $user = Yii::app()->user->getModel();
        $moduleId = Yii::app()->request->getParam('moduleId', "");

        if ($user->isModuleEnabled($moduleId)) {
            $user->uninstallModule($moduleId);
        }

        $this->redirect($this->createUrl('//user/account/editModules', array()));
    }

    /**
     * Edit Users Profile
     */
    public function actionEdit()
    {

        $_POST = Yii::app()->input->stripClean($_POST);

        $profile = Profile::model()->findByAttributes(array('user_id' => Yii::app()->user->id));
        if ($profile == null) {
            $profile = new Profile;
            $profile->user_id = Yii::app()->user->id;
        }

        // Get Form Definition
        $definition = $profile->getFormDefinition();
        $definition['buttons'] = array(
            'save' => array(
                'type' => 'submit',
                'label' => Yii::t('UserModule.account', 'Save profile'),
                'class' => 'btn btn-primary'
            ),
        );

        // Create Form
        $form = new HForm($definition, $profile);
        if ($form->submitted('save') && $form->validate()) {
            $this->forcePostRequest();
            $profile->save();

            // set flash message
            Yii::app()->user->setFlash('data-saved', Yii::t('base', 'Saved'));
        }

        $this->render('edit', array('form' => $form));
    }

    /**
     * Delete Action
     *
     * Its only possible if the user is not owner of a workspace.
     */
    public function actionDelete()
    {

        $isSpaceOwner = false;

        $user = Yii::app()->user->getModel();

        if ($user->auth_mode != User::AUTH_MODE_LOCAL) {
            throw new CHttpException(500, 'This is not a local account! You cannot delete it. (e.g. LDAP)!');
        }

        foreach (SpaceMembership::GetUserSpaces() as $workspace) {
            // Oups, we are owner in this workspace!
            if ($workspace->isOwner($user->id)) {
                $isSpaceOwner = true;
            }
        }

        $model = new AccountDeleteForm;

        if (!$isSpaceOwner) {
            // Uncomment the following line if AJAX validation is needed
            // $this->performAjaxValidation($model);

            if (isset($_POST['AccountDeleteForm'])) {
                $_POST['AccountDeleteForm'] = Yii::app()->input->stripClean($_POST['AccountDeleteForm']);
                $model->attributes = $_POST['AccountDeleteForm'];

                if ($model->validate()) {
                    $user->delete();

                    Yii::app()->user->logout();
                    $this->redirect(Yii::app()->homeUrl);
                }
            }
        }

        $this->render('delete', array(
            'model' => $model,
            'isSpaceOwner' => $isSpaceOwner
        ));
    }

    /**
     * Change EMail Options
     *
     * @todo Add Group
     */
    public function actionEmailing()
    {

        $model = User::model()->findByPk(Yii::app()->user->id);
        $model->scenario = 'edit';

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-editAccount-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['User'])) {

            $_POST['User'] = Yii::app()->input->stripClean($_POST['User']);
            $model->attributes = $_POST['User'];


            if ($model->validate()) {

                // Create User
                $model->save();

                // Reload User in Session
                Yii::app()->user->reload();

                // set flash message
                Yii::app()->user->setFlash('data-saved', Yii::t('base', 'Saved'));

                $this->render('emailing', array('model' => $model));

                // form inputs are valid, do something here
                return;
            }
        }

        $this->render('emailing', array('model' => $model));
    }

    /**
     * Change Current Password
     *
     */
    public function actionChangeEmail()
    {

        $user = User::model()->findByPk(Yii::app()->user->id);
        if ($user->auth_mode != User::AUTH_MODE_LOCAL) {
            throw new CHttpException(500, Yii::t('UserModule.base', 'You cannot change your e-mail address here.'));
        }

        $model = new AccountChangeEmailForm;

        if (isset($_POST['AccountChangeEmailForm'])) {

            $_POST['AccountChangeEmailForm'] = Yii::app()->input->stripClean($_POST['AccountChangeEmailForm']);
            $model->attributes = $_POST['AccountChangeEmailForm'];

            if ($model->validate()) {

                $model->sendChangeEmail();

                $this->render('changeEmail_success', array('model' => $model));

                // form inputs are valid, do something here
                return;
            }
        }

        $this->render('changeEmail', array('model' => $model));
    }

    /**
     * After the user validated his email
     *
     */
    public function actionChangeEmailValidate()
    {

        $token = $_GET['token'];
        $email = $_GET['email'];

        $user = User::model()->findByPk(Yii::app()->user->id);

        if ($user->auth_mode != User::AUTH_MODE_LOCAL) {
            throw new CHttpException(500, Yii::t('UserModule.base', 'You cannot change your e-mail address here.'));
        }

        // Check if Token is valid
        if (md5(HSetting::Get('secret') . $user->guid . $email) != $token) {
            throw new CHttpException(404, Yii::t('UserModule.base', 'Invalid link! Please make sure that you entered the entire url.'));
        }

        // Check if E-Mail is in use, e.g. by other user
        $emailAvailablyCheck = User::model()->findByAttributes(array('email' => $email));
        if ($emailAvailablyCheck != null) {
            throw new CHttpException(404, Yii::t('UserModule.base', 'The entered e-mail address is already in use by another user.'));
        }

        $user->email = $email;
        $user->save();

        $this->render('changeEmailValidate', array('newEmail' => $email));
    }

    /**
     * Change users current password
     */
    public function actionChangePassword()
    {

        if (Yii::app()->user->authMode != User::AUTH_MODE_LOCAL) {
            throw new CHttpException(500, Yii::t('UserModule.account', 'You cannot change your password here.'));
        }

        $userPassword = new UserPassword('changePassword');

        if (isset($_POST['UserPassword'])) {
            $userPassword->attributes = $_POST['UserPassword'];

            if ($userPassword->validate()) {
                $userPassword->user_id = Yii::app()->user->id;
                $userPassword->setPassword($userPassword->newPassword);
                $userPassword->save();

                return $this->render('changePassword_success');
            }
        }

        $this->render('changePassword', array('model' => $userPassword));
    }

}

?>
