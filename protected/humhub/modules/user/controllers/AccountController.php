<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use Yii;
use \humhub\components\Controller;
use \yii\helpers\Url;
use \yii\web\HttpException;
use \humhub\modules\user\models\User;

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

    public $subLayout = "@humhub/modules/user/views/account/_layout";

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
            ]
        ];
    }

    /**
     * Edit Users Profile
     */
    public function actionEdit()
    {

        $user = Yii::$app->user->getIdentity();

        // Get Form Definition
        $definition = $user->profile->getFormDefinition();
        $definition['buttons'] = array(
            'save' => array(
                'type' => 'submit',
                'label' => Yii::t('UserModule.controllers_AccountController', 'Save profile'),
                'class' => 'btn btn-primary'
            ),
        );

        $form = new \humhub\compat\HForm($definition, $user->profile);
        $form->showErrorSummary = true;
        if ($form->submitted('save') && $form->validate() && $form->save()) {

            // Trigger search refresh
            $user->save();

            Yii::$app->getSession()->setFlash('data-saved', Yii::t('UserModule.controllers_AccountController', 'Saved'));
            return $this->redirect(Url::to(['edit']));
        }

        return $this->render('edit', array('hForm' => $form));
    }

    /**
     * Change Account
     *
     * @todo Add Group
     */
    public function actionEditSettings()
    {
        $user = Yii::$app->user->getIdentity();

        $model = new \humhub\modules\user\models\forms\AccountSettings();
        $model->language = $user->language;
        if ($model->language == "") {
            $model->language = \humhub\models\Setting::Get('defaultLanguage');
        }
        $model->timeZone = $user->time_zone;
        if ($model->timeZone == "") {
            $model->timeZone = \humhub\models\Setting::Get('timeZone');
        }
        $model->show_introduction_tour = $user->getSetting("hideTourPanel", "tour");

        $model->tags = $user->tags;
        $model->show_introduction_tour = $user->getSetting("hideTourPanel", "tour");
        $model->show_share_panel = $user->getSetting("hideSharePanel", "share");
        $model->visibility = $user->visibility;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user->setSetting('hideTourPanel', $model->show_introduction_tour, "tour");
            $user->setSetting("hideSharePanel", $model->show_share_panel, "share");
            $user->language = $model->language;
            $user->tags = $model->tags;
            $user->time_zone = $model->timeZone;
            $user->visibility = $model->visibility;
            $user->save();

            Yii::$app->getSession()->setFlash('data-saved', Yii::t('UserModule.controllers_AccountController', 'Saved'));
        }

        return $this->render('editSettings', array('model' => $model, 'languages' => Yii::$app->params['availableLanguages']));
    }

    /**
     * Allows the user to enable user specifc modules
     */
    public function actionEditModules()
    {
        $user = Yii::$app->user->getIdentity();
        $availableModules = $user->getAvailableModules();

        return $this->render('editModules', array('user' => $user, 'availableModules' => $availableModules));
    }

    public function actionEnableModule()
    {
        $this->forcePostRequest();

        $user = Yii::$app->user->getIdentity();
        $moduleId = Yii::$app->request->get('moduleId');

        if (!$user->isModuleEnabled($moduleId)) {
            $user->enableModule($moduleId);
        }

        return $this->redirect(Url::toRoute('/user/account/edit-modules'));
    }

    public function actionDisableModule()
    {
        $this->forcePostRequest();

        $user = Yii::$app->user->getIdentity();
        $moduleId = Yii::$app->request->get('moduleId');

        if ($user->isModuleEnabled($moduleId) && $user->canDisableModule($moduleId)) {
            $user->disableModule($moduleId);
        }

        return $this->redirect(Url::toRoute('/user/account/edit-modules'));
    }

    /**
     * Delete Action
     *
     * Its only possible if the user is not owner of a workspace.
     */
    public function actionDelete()
    {

        $isSpaceOwner = false;
        $user = Yii::$app->user->getIdentity();

        if ($user->auth_mode != User::AUTH_MODE_LOCAL) {
            throw new HttpException(500, 'This is not a local account! You cannot delete it. (e.g. LDAP)!');
        }

        foreach (\humhub\modules\space\models\Membership::GetUserSpaces() as $space) {
            if ($space->isSpaceOwner($user->id)) {
                $isSpaceOwner = true;
            }
        }

        $model = new \humhub\modules\user\models\forms\AccountDelete;

        if (!$isSpaceOwner && $model->load(Yii::$app->request->post()) && $model->validate()) {
            $user->delete();
            Yii::$app->user->logout();
            return $this->redirect(Yii::$app->homeUrl);
        }

        return $this->render('delete', array(
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
        $user = Yii::$app->user->getIdentity();
        $model = new \humhub\modules\user\models\forms\AccountEmailing();

        $model->receive_email_activities = $user->getSetting("receive_email_activities", 'core', \humhub\models\Setting::Get('receive_email_activities', 'mailing'));
        $model->receive_email_notifications = $user->getSetting("receive_email_notifications", 'core', \humhub\models\Setting::Get('receive_email_notifications', 'mailing'));
        $model->enable_html5_desktop_notifications = $user->getSetting("enable_html5_desktop_notifications", 'core', \humhub\models\Setting::Get('enable_html5_desktop_notifications', 'notification'));

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user->setSetting("receive_email_activities", $model->receive_email_activities);
            $user->setSetting("receive_email_notifications", $model->receive_email_notifications);
            $user->setSetting('enable_html5_desktop_notifications', $model->enable_html5_desktop_notifications);

            Yii::$app->getSession()->setFlash('data-saved', Yii::t('UserModule.controllers_AccountController', 'Saved'));
        }
        return $this->render('emailing', array('model' => $model));
    }

    /**
     * Change Current Password
     *
     */
    public function actionChangeEmail()
    {
        $user = Yii::$app->user->getIdentity();
        if ($user->auth_mode != User::AUTH_MODE_LOCAL) {
            throw new HttpException(500, Yii::t('UserModule.controllers_AccountController', 'You cannot change your e-mail address here.'));
        }

        $model = new \humhub\modules\user\models\forms\AccountChangeEmail;

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->sendChangeEmail()) {
            return $this->render('changeEmail_success', array('model' => $model));
        }

        return $this->render('changeEmail', array('model' => $model));
    }

    /**
     * After the user validated his email
     *
     */
    public function actionChangeEmailValidate()
    {
        $user = Yii::$app->user->getIdentity();

        if ($user->auth_mode != User::AUTH_MODE_LOCAL) {
            throw new CHttpException(500, Yii::t('UserModule.controllers_AccountController', 'You cannot change your e-mail address here.'));
        }

        $token = Yii::$app->request->get('token');
        $email = Yii::$app->request->get('email');

        // Check if Token is valid
        if (md5(\humhub\models\Setting::Get('secret') . $user->guid . $email) != $token) {
            throw new HttpException(404, Yii::t('UserModule.controllers_AccountController', 'Invalid link! Please make sure that you entered the entire url.'));
        }

        // Check if E-Mail is in use, e.g. by other user
        $emailAvailablyCheck = \humhub\modules\user\models\User::findOne(['email' => $email]);
        if ($emailAvailablyCheck != null) {
            throw new HttpException(404, Yii::t('UserModule.controllers_AccountController', 'The entered e-mail address is already in use by another user.'));
        }

        $user->email = $email;
        $user->save();

        return $this->render('changeEmailValidate', array('newEmail' => $email));
    }

    /**
     * Change users current password
     */
    public function actionChangePassword()
    {
        $user = Yii::$app->user->getIdentity();

        if ($user->auth_mode != User::AUTH_MODE_LOCAL) {
            throw new CHttpException(500, Yii::t('UserModule.controllers_AccountController', 'You cannot change your e-mail address here.'));
        }

        $userPassword = new \humhub\modules\user\models\Password();
        $userPassword->scenario = 'changePassword';

        if ($userPassword->load(Yii::$app->request->post()) && $userPassword->validate()) {
            $userPassword->user_id = Yii::$app->user->id;
            $userPassword->setPassword($userPassword->newPassword);
            $userPassword->save();

            return $this->render('changePassword_success');
        }

        return $this->render('changePassword', array('model' => $userPassword));
    }

    /**
     * Crops the banner image of the user
     */
    public function actionCropBannerImage()
    {
        $model = new \humhub\models\forms\CropProfileImage();
        $profileImage = new \humhub\libs\ProfileBannerImage($this->getUser()->guid);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $profileImage->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
            return $this->htmlRedirect($this->getUser()->getUrl());
        }

        return $this->renderAjax('cropBannerImage', ['model' => $model, 'profileImage' => $profileImage, 'user' => $this->getUser()]);
    }

    /**
     * Handle the banner image upload
     */
    public function actionBannerImageUpload()
    {
        \Yii::$app->response->format = 'json';

        $model = new \humhub\models\forms\UploadProfileImage();
        $json = array();

        $files = \yii\web\UploadedFile::getInstancesByName('bannerfiles');
        $file = $files[0];
        $model->image = $file;

        if ($model->validate()) {
            $profileImage = new \humhub\libs\ProfileBannerImage($this->getUser()->guid);
            $profileImage->setNew($model->image);

            $json['error'] = false;
            $json['name'] = "";
            $json['url'] = $profileImage->getUrl();
            $json['size'] = $model->image->size;
            $json['deleteUrl'] = "";
            $json['deleteType'] = "";
        } else {
            $json['error'] = true;
            $json['errors'] = $model->getErrors();
        }

        return ['files' => $json];
    }

    /**
     * Handle the profile image upload
     */
    public function actionProfileImageUpload()
    {
        \Yii::$app->response->format = 'json';

        $model = new \humhub\models\forms\UploadProfileImage();

        $json = array();

        $files = \yii\web\UploadedFile::getInstancesByName('profilefiles');
        $file = $files[0];
        $model->image = $file;

        if ($model->validate()) {

            $json['error'] = false;

            $profileImage = new \humhub\libs\ProfileImage($this->getUser()->guid);
            $profileImage->setNew($model->image);

            $json['name'] = "";
            $json['url'] = $profileImage->getUrl();
            $json['size'] = $model->image->size;
            $json['deleteUrl'] = "";
            $json['deleteType'] = "";
        } else {
            $json['error'] = true;
            $json['errors'] = $model->getErrors();
        }

        return array('files' => $json);
    }

    /**
     * Crops the profile image of the user
     */
    public function actionCropProfileImage()
    {
        $model = new \humhub\models\forms\CropProfileImage();
        $profileImage = new \humhub\libs\ProfileImage($this->getUser()->guid);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $profileImage->cropOriginal($model->cropX, $model->cropY, $model->cropH, $model->cropW);
            return $this->htmlRedirect($this->getUser()->getUrl());
        }

        return $this->renderAjax('cropProfileImage', array('model' => $model, 'profileImage' => $profileImage, 'user' => $this->getUser()));
    }

    /**
     * Deletes the profile image or profile banner
     */
    public function actionDeleteProfileImage()
    {
        \Yii::$app->response->format = 'json';

        $this->forcePostRequest();

        $type = Yii::$app->request->get('type', 'profile');

        $json = array('type' => $type);

        $image = null;
        if ($type == 'profile') {
            $image = new \humhub\libs\ProfileImage($this->getUser()->guid);
        } elseif ($type == 'banner') {
            $image = new \humhub\libs\ProfileBannerImage($this->getUser()->guid);
        }

        if ($image) {
            $image->delete();
            $json['defaultUrl'] = $image->getUrl();
        }

        return $json;
    }

    /**
     * Returns the current user of this account
     * 
     * An administration can also pass a user id via GET parameter to change users
     * accounts settings.
     * 
     * @return User the user
     */
    public function getUser()
    {
        if (Yii::$app->request->get('userGuid') != '' && Yii::$app->user->getIdentity()->super_admin === 1) {
            $user = User::findOne(['guid' => Yii::$app->request->get('userGuid')]);
            if ($user === null) {
                throw new HttpException(404, 'Could not find user!');
            }
            return $user;
        }

        return Yii::$app->user->getIdentity();
    }

}

?>
