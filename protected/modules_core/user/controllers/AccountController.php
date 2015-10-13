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

        $model = new AccountSettingsForm();
        $model->language = Yii::app()->user->getModel()->language;
        if ($model->language == "") {
            $model->language = HSetting::Get('defaultLanguage');
        }
        
        $model->tags = Yii::app()->user->getModel()->tags;
        $model->show_introduction_tour = Yii::app()->user->getModel()->getSetting("hideTourPanel", "tour");
        $model->visibility = Yii::app()->user->getModel()->visibility;

        if (isset($_POST['AccountSettingsForm'])) {

            $_POST['AccountSettingsForm'] = Yii::app()->input->stripClean($_POST['AccountSettingsForm']);
            $model->attributes = $_POST['AccountSettingsForm'];

            if ($model->validate()) {

                Yii::app()->user->getModel()->setSetting('hideTourPanel', $model->show_introduction_tour, "tour");

                $user = Yii::app()->user->getModel();
                $user->language = $model->language;
                $user->tags = $model->tags;
                $user->visibility = $model->visibility;
                $user->save();

                Yii::app()->user->reload();

                Yii::app()->user->setFlash('data-saved', Yii::t('UserModule.controllers_AccountController', 'Saved'));
                $this->refresh();
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
        $this->forcePostRequest();

        $user = Yii::app()->user->getModel();
        $moduleId = Yii::app()->request->getParam('moduleId', "");

        if (!$user->isModuleEnabled($moduleId)) {
            $user->enableModule($moduleId);
        }

        $this->redirect($this->createUrl('//user/account/editModules', array()));
    }

    public function actionDisableModule()
    {
        $this->forcePostRequest();

        $user = Yii::app()->user->getModel();
        $moduleId = Yii::app()->request->getParam('moduleId', "");

        if ($user->isModuleEnabled($moduleId) && $user->canDisableModule($moduleId)) {
            $user->disableModule($moduleId);
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
                'label' => Yii::t('UserModule.controllers_AccountController', 'Save profile'),
                'class' => 'btn btn-primary'
            ),
        );

        // Create Form
        $form = new HForm($definition, $profile);
        $form->showErrorSummary = true;
        if ($form->submitted('save') && $form->validate()) {
            $this->forcePostRequest();
            $profile->save();

            // Save user to force reindex to search
            $user = User::model()->findByPk(Yii::app()->user->id);
            $user->save();
            
            // set flash message
            Yii::app()->user->setFlash('data-saved', Yii::t('UserModule.controllers_AccountController', 'Saved'));
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

        foreach (SpaceMembership::GetUserSpaces() as $space) {
            // Oups, we are owner in this workspace!
            if ($space->isSpaceOwner($user->id)) {
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

        $user = Yii::app()->user->getModel();
        $model = new AccountEmailingForm();

        $model->receive_email_activities = $user->getSetting("receive_email_activities", 'core', HSetting::Get('receive_email_activities', 'mailing'));
        $model->receive_email_notifications = $user->getSetting("receive_email_notifications", 'core', HSetting::Get('receive_email_notifications', 'mailing'));
        $model->enable_html5_desktop_notifications = $user->getSetting("enable_html5_desktop_notifications", 'core', HSetting::Get('enable_html5_desktop_notifications', 'notification'));

        if (isset($_POST['AccountEmailingForm'])) {
            $model->attributes = Yii::app()->input->stripClean($_POST['AccountEmailingForm']);

            if ($model->validate()) {
                $user->setSetting("receive_email_activities", $model->receive_email_activities);
                $user->setSetting("receive_email_notifications", $model->receive_email_notifications);
                $user->setSetting('enable_html5_desktop_notifications', $model->enable_html5_desktop_notifications);

                Yii::app()->user->setFlash('data-saved', Yii::t('UserModule.controllers_AccountController', 'Saved'));
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
            throw new CHttpException(500, Yii::t('UserModule.controllers_AccountController', 'You cannot change your e-mail address here.'));
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
     * Change Current Username
     *
     */
    public function actionChangeUsername()
    {
    
    	$user = User::model()->findByPk(Yii::app()->user->id);
    	if ($user->auth_mode != User::AUTH_MODE_LOCAL) {
    		throw new CHttpException(500, Yii::t('UserModule.controllers_AccountController', 'You cannot change your e-mail address here.'));
    	}
    
    	$model = new AccountChangeUsernameForm;
    
    	if (isset($_POST['AccountChangeUsernameForm'])) {
    		$_POST['AccountChangeUsernameForm'] = Yii::app()->input->stripClean($_POST['AccountChangeUsernameForm']);
    		$model->attributes = $_POST['AccountChangeUsernameForm'];
    
    		if ($model->validate()) {
    
    			$model->changeUsername();
    			$this->render('changeUsername_success', array('model' => $model));
    			
    			return;
    		}
    	}
    
    	$this->render('changeUsername', array('model' => $model));
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
            throw new CHttpException(500, Yii::t('UserModule.controllers_AccountController', 'You cannot change your e-mail address here.'));
        }

        // Check if Token is valid
        if (md5(HSetting::Get('secret') . $user->guid . $email) != $token) {
            throw new CHttpException(404, Yii::t('UserModule.controllers_AccountController', 'Invalid link! Please make sure that you entered the entire url.'));
        }

        // Check if E-Mail is in use, e.g. by other user
        $emailAvailablyCheck = User::model()->findByAttributes(array('email' => $email));
        if ($emailAvailablyCheck != null) {
            throw new CHttpException(404, Yii::t('UserModule.controllers_AccountController', 'The entered e-mail address is already in use by another user.'));
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
            throw new CHttpException(500, Yii::t('UserModule.controllers_AccountController', 'You cannot change your password here.'));
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

    /**
     * Crops the banner image of the user
     */
    public function actionCropBannerImage()
    {

        $model = new CropProfileImageForm;
        $profileImage = new ProfileBannerImage(Yii::app()->user->guid);

        if (isset($_POST['CropProfileImageForm'])) {
            $_POST['CropProfileImageForm'] = Yii::app()->input->stripClean($_POST['CropProfileImageForm']);
            $model->attributes = $_POST['CropProfileImageForm'];
            if ($model->validate()) {
                $profileImage->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
                $this->htmlRedirect(Yii::app()->user->getModel()->getUrl());
            }
        }

        $this->renderPartial('cropBannerImage', array('model' => $model, 'profileImage' => $profileImage, 'user' => Yii::app()->user->getModel()), false, true);
    }

    /**
     * Handle the banner image upload
     */
    public function actionBannerImageUpload()
    {

        $model = new UploadProfileImageForm();

        $json = array();

        $files = CUploadedFile::getInstancesByName('bannerfiles');
        $file = $files[0];
        $model->image = $file;

        if ($model->validate()) {

            $json['error'] = false;

            $profileImage = new ProfileBannerImage(Yii::app()->user->guid);
            $profileImage->setNew($model->image);

            $json['name'] = "";
            $json['url'] = $profileImage->getUrl();
            $json['size'] = $model->image->getSize();
            $json['deleteUrl'] = "";
            $json['deleteType'] = "";
        } else {
            $json['error'] = true;
            $json['errors'] = $model->getErrors();
        }


        return $this->renderJson(array('files' => $json));
    }

    /**
     * Handle the profile image upload
     */
    public function actionProfileImageUpload()
    {

        $model = new UploadProfileImageForm();

        $json = array();

        //$model->image = CUploadedFile::getInstance($model, 'image');
        $files = CUploadedFile::getInstancesByName('profilefiles');
        $file = $files[0];
        $model->image = $file;

        if ($model->validate()) {

            $json['error'] = false;

            $profileImage = new ProfileImage(Yii::app()->user->guid);
            $profileImage->setNew($model->image);

            $json['name'] = "";
            $json['url'] = $profileImage->getUrl();
            $json['size'] = $model->image->getSize();
            $json['deleteUrl'] = "";
            $json['deleteType'] = "";
        } else {
            $json['error'] = true;
            $json['errors'] = $model->getErrors();
        }


        return $this->renderJson(array('files' => $json));
    }

    /**
     * Crops the profile image of the user
     */
    public function actionCropProfileImage()
    {

        $model = new CropProfileImageForm;
        $profileImage = new ProfileImage(Yii::app()->user->guid);

        if (isset($_POST['CropProfileImageForm'])) {
            $_POST['CropProfileImageForm'] = Yii::app()->input->stripClean($_POST['CropProfileImageForm']);
            $model->attributes = $_POST['CropProfileImageForm'];
            if ($model->validate()) {
                $profileImage->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
                $this->htmlRedirect(Yii::app()->user->getModel()->getUrl());
            }
        }

        $this->renderPartial('cropProfileImage', array('model' => $model, 'profileImage' => $profileImage, 'user' => Yii::app()->user->getModel()), false, true);
    }

    /**
     * Deletes the profile image or profile banner
     */
    public function actionDeleteProfileImage()
    {
        $this->forcePostRequest();

        $type = Yii::app()->request->getParam('type', 'profile');

        $json = array('type' => $type);

        $image = NULL;
        if ($type == 'profile') {
            $image = new ProfileImage(Yii::app()->user->guid);
        } elseif ($type == 'banner') {
            $image = new ProfileBannerImage(Yii::app()->user->guid);
        }

        if ($image) {
            $image->delete();
            $json['defaultUrl'] = $image->getUrl();
        }

        $this->renderJson($json);
    }

}

?>
