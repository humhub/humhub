<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use Yii;
use yii\web\HttpException;
use humhub\modules\user\components\BaseAccountController;
use humhub\modules\user\models\User;

/**
 * AccountController provides all standard actions for the current logged in
 * user account.
 *
 * @author Luke
 * @since 0.5
 */
class AccountController extends BaseAccountController
{

    public function init()
    {
        $this->setActionTitles([
            'edit' => Yii::t('UserModule.base', 'Profile'),
            'edit-settings' => Yii::t('UserModule.base', 'Settings'),
            'security' => Yii::t('UserModule.base', 'Security'),
            'connected-accounts' => Yii::t('UserModule.base', 'Connected accounts'),
            'edit-modules' => Yii::t('UserModule.base', 'Modules'),
            'delete' => Yii::t('UserModule.base', 'Delete'),
            'emailing' => Yii::t('UserModule.base', 'Notifications'),
            'change-email' => Yii::t('UserModule.base', 'Email'),
            'change-email-validate' => Yii::t('UserModule.base', 'Email'),
            'change-password' => Yii::t('UserModule.base', 'Password'),
        ]);
        return parent::init();
    }

    /**
     * Redirect to current users profile
     */
    public function actionIndex()
    {
        return $this->redirect(Yii::$app->user->getIdentity()->getUrl());
    }

    /**
     * Edit Users Profile
     */
    public function actionEdit()
    {
        $user = Yii::$app->user->getIdentity();
        $user->profile->scenario = 'editProfile';

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
            return $this->redirect(['edit']);
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
            $model->language = Yii::$app->settings->get('defaultLanguage');
        }
        $model->timeZone = $user->time_zone;
        if ($model->timeZone == "") {
            $model->timeZone = Yii::$app->settings->get('timeZone');
        }

        $model->tags = $user->tags;
        $model->show_introduction_tour = Yii::$app->getModule('tour')->settings->contentContainer($user)->get("hideTourPanel");
        $model->visibility = $user->visibility;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::$app->getModule('tour')->settings->contentContainer($user)->set('hideTourPanel', $model->show_introduction_tour);
            $user->language = $model->language;
            $user->tags = $model->tags;
            $user->time_zone = $model->timeZone;
            $user->visibility = $model->visibility;
            $user->save();
           
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('UserModule.controllers_AccountController', 'Saved'));
            return $this->redirect(['edit-settings']);
        }

        return $this->render('editSettings', array('model' => $model, 'languages' => Yii::$app->i18n->getAllowedLanguages()));
    }

    /**
     * Change Account
     *
     * @todo Add Group
     */
    public function actionSecurity()
    {
        $groups = [];

        if (Yii::$app->getModule('friendship')->getIsEnabled()) {
            $groups[User::USERGROUP_FRIEND] = 'Friends';
        }
        $groups[User::USERGROUP_USER] = 'Members';
        $groups[User::USERGROUP_GUEST] = 'Guests';

        $currentGroup = Yii::$app->request->get('groupId');
        if ($currentGroup == '' || !isset($groups[$currentGroup])) {
            $currentGroup = User::USERGROUP_USER;
        }

        // Handle permission state change
        if (Yii::$app->request->post('dropDownColumnSubmit')) {
            Yii::$app->response->format = 'json';
            $permission = $this->user->permissionManager->getById(Yii::$app->request->post('permissionId'), Yii::$app->request->post('moduleId'));
            if ($permission === null) {
                throw new \yii\web\HttpException(500, 'Could not find permission!');
            }
            $this->user->permissionManager->setGroupState($currentGroup, $permission, Yii::$app->request->post('state'));
            return [];
        }

        return $this->render('security', ['user' => $this->getUser(), 'groups' => $groups, 'group' => $currentGroup]);
    }

    public function actionConnectedAccounts()
    {
        if (Yii::$app->request->isPost && Yii::$app->request->get('disconnect')) {
            foreach (Yii::$app->user->getAuthClients() as $authClient) {
                if ($authClient->getId() == Yii::$app->request->get('disconnect')) {
                    \humhub\modules\user\authclient\AuthClientHelpers::removeAuthClientForUser($authClient, Yii::$app->user->getIdentity());
                }
            }
            return $this->redirect(['connected-accounts']);
        }
        $clients = [];
        foreach (Yii::$app->get('authClientCollection')->getClients() as $client) {
            if (!$client instanceof humhub\modules\user\authclient\BaseFormAuth && !$client instanceof \humhub\modules\user\authclient\interfaces\PrimaryClient) {
                $clients[] = $client;
            }
        }

        $currentAuthProviderId = "";
        if (Yii::$app->user->getCurrentAuthClient() !== null) {
            $currentAuthProviderId = Yii::$app->user->getCurrentAuthClient()->getId();
        }

        $activeAuthClientIds = [];
        foreach (Yii::$app->user->getAuthClients() as $authClient) {
            $activeAuthClientIds[] = $authClient->getId();
        }

        return $this->render('connected-accounts', [
                    'authClients' => $clients,
                    'currentAuthProviderId' => $currentAuthProviderId,
                    'activeAuthClientIds' => $activeAuthClientIds
        ]);
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

        return $this->redirect(['/user/account/edit-modules']);
    }

    public function actionDisableModule()
    {
        $this->forcePostRequest();

        $user = Yii::$app->user->getIdentity();
        $moduleId = Yii::$app->request->get('moduleId');

        if ($user->isModuleEnabled($moduleId) && $user->canDisableModule($moduleId)) {
            $user->disableModule($moduleId);
        }

        return $this->redirect(['/user/account/edit-modules']);
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

        if (!Yii::$app->user->canDeleteAccount()) {
            throw new HttpException(500, 'Account deletion not allowed');
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
            return $this->goHome();
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
        $model = new \humhub\modules\user\models\forms\AccountEmailing();
        
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->save()) {
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
        if (!Yii::$app->user->canChangeEmail()) {
            throw new HttpException(500, 'Change E-Mail is not allowed');
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
        if (!Yii::$app->user->canChangeEmail()) {
            throw new HttpException(500, 'Change E-Mail is not allowed');
        }

        $token = Yii::$app->request->get('token');
        $email = Yii::$app->request->get('email');

        $user = Yii::$app->user->getIdentity();

        // Check if Token is valid
        if (md5(Yii::$app->settings->get('secret') . $user->guid . $email) != $token) {
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
        if (!Yii::$app->user->canChangePassword()) {
            throw new HttpException(500, 'Password change is not allowed');
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
        if (Yii::$app->request->get('userGuid') != '' && Yii::$app->user->getIdentity()->isSystemAdmin()) {
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
