<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use Collator;
use humhub\compat\HForm;
use humhub\modules\content\widgets\ContainerTagPicker;
use humhub\modules\user\components\BaseAccountController;
use humhub\modules\user\models\forms\AccountChangeEmail;
use humhub\modules\user\models\forms\AccountChangeUsername;
use humhub\modules\user\models\forms\AccountSettings;
use humhub\modules\user\models\User;
use humhub\modules\user\services\UserSourceService;
use Throwable;
use Yii;
use yii\web\HttpException;
use yii\web\Response;

/**
 * AccountController provides all standard actions for the current logged in
 * user account.
 *
 * @author Luke
 * @since 0.5
 */
class AccountController extends BaseAccountController
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setActionTitles([
            'edit' => Yii::t('UserModule.base', 'Profile'),
            'edit-settings' => Yii::t('UserModule.base', 'Settings'),
            'edit-modules' => Yii::t('UserModule.base', 'Modules'),
            'notification' => Yii::t('UserModule.base', 'Notifications'),
            'change-email' => Yii::t('UserModule.base', 'Email'),
            'change-email-validate' => Yii::t('UserModule.base', 'Email'),
        ]);
        return parent::init();
    }

    /**
     * Redirect to current users profile
     * @throws Throwable
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        return $this->redirect(Yii::$app->user->getIdentity()->getUrl());
    }

    /**
     * Edit Users Profile
     * @throws Throwable
     */
    public function actionEdit()
    {
        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $user->profile->scenario = 'editProfile';

        // Get Form Definition
        $definition = $user->profile->getFormDefinition();
        $definition['buttons'] = [
            'save' => [
                'type' => 'submit',
                'label' => Yii::t('UserModule.account', 'Save profile'),
                'class' => 'btn btn-primary',
            ],
        ];

        $form = new HForm($definition, $user->profile);
        $form->showErrorSummary = true;
        if ($form->submitted('save') && $form->validate() && $form->save()) {
            // Trigger search refresh
            $user->save();

            $this->view->saved();
            return $this->redirect(['edit']);
        }

        return $this->render('edit', ['hForm' => $form]);
    }

    /**
     * Change Account
     *
     * @todo Add Group
     */
    public function actionEditSettings()
    {
        /** @var User $user */
        $user = Yii::$app->user->getIdentity();

        $model = new AccountSettings(['user' => $user]);
        $model->language = Yii::$app->i18n->getAllowedLanguage($user->language);
        $model->timeZone = $user->time_zone;
        if (empty($model->timeZone)) {
            $model->timeZone = Yii::$app->settings->get('defaultTimeZone');
        }

        $model->tags = $user->getTags();
        $model->markdownEditorMode = $user->settings->get("markdownEditorMode");
        $model->show_introduction_tour = Yii::$app->getModule('tour')->settings->contentContainer($user)->get("hideTourPanel");

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user->settings->set('markdownEditorMode', $model->markdownEditorMode);
            Yii::$app->getModule('tour')->settings->contentContainer($user)->set('hideTourPanel', $model->show_introduction_tour);

            $user->scenario = User::SCENARIO_EDIT_ACCOUNT_SETTINGS;
            $user->language = $model->language;
            $user->tagsField = $model->tags;
            $user->time_zone = $model->timeZone;
            $user->save();

            $this->view->saved();
            return $this->redirect(['edit-settings']);
        }

        // Sort countries list based on user language
        $languages = Yii::$app->i18n->getAllowedLanguages();
        $col = new Collator(Yii::$app->language);
        $col->asort($languages);

        return $this->render('editSettings', [
            'model' => $model,
            'languages' => $languages,
        ]);
    }

    /**
     * Returns user tags list in JSON format filtered by keyword
     */
    public function actionSearchTagsJson()
    {
        $keyword = Yii::$app->request->get('keyword', '');

        $guid = Yii::$app->request->get('guid');
        $user = $guid ? User::findOne(['guid' => $guid]) : null;

        return $this->asJson($user
            ? ContainerTagPicker::searchTagsByContainer($user, $keyword)
            : ContainerTagPicker::searchTagsByContainerClass(User::class, $keyword));
    }

    /**
     * @deprecated since 1.20, moved to [[SecurityController::actionPrivacy()]]
     */
    public function actionPermissions()
    {
        return $this->redirect(['/user/security/privacy'], 301);
    }

    /**
     * @deprecated since 1.20, moved to [[SecurityController::actionLogin()]]
     */
    public function actionConnectedAccounts()
    {
        return $this->redirect(['/user/security/login'], 301);
    }

    /**
     * Allows the user to enable user specifc modules
     */
    public function actionEditModules()
    {
        $this->subLayout = '@humhub/modules/user/views/account/_userModulesLayout';

        /* @var User $user */
        $user = Yii::$app->user->getIdentity();

        return $this->render('editModules', [
            'user' => $user,
            'modules' => $user->moduleManager->getAvailable(),
        ]);
    }

    /**
     * @return array|AccountController|\yii\console\Response|Response
     * @throws HttpException
     * @throws Throwable
     */
    public function actionEnableModule()
    {
        $this->forcePostRequest();

        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $moduleId = Yii::$app->request->get('moduleId');

        $user->moduleManager->enable($moduleId);

        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['/user/account/edit-modules']);
        }

        return $this->asJson(['success' => true]);
    }

    /**
     * @return array|AccountController|\yii\console\Response|Response
     * @throws HttpException
     * @throws Throwable
     */
    public function actionDisableModule()
    {
        $this->forcePostRequest();

        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $moduleId = Yii::$app->request->get('moduleId');

        $user->moduleManager->disable($moduleId);

        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['/user/account/edit-modules']);
        } else {
            Yii::$app->response->format = 'json';
            return ['success' => true];
        }
    }

    /**
     * @deprecated since 1.20, moved to [[SecurityController::actionDelete()]]
     */
    public function actionDelete()
    {
        return $this->redirect(['/user/security/delete'], 301);
    }

    /**
     * Change Current Username
     */
    public function actionChangeUsername()
    {
        if (!UserSourceService::getForUser()->canChangeUsername()) {
            throw new HttpException(500, 'Change Username is not allowed');
        }

        $model = new AccountChangeUsername();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->sendChangeUsername()) {
            return $this->render('changeUsername_success', ['model' => $model]);
        }

        $this->view->warn(Yii::t('UserModule.account', 'Changing the username can make some links unusable, for example old links to the profile.'));
        return $this->render('changeUsername', ['model' => $model]);
    }

    /**
     * Change Current E-mail
     */
    public function actionChangeEmail()
    {
        if (!UserSourceService::getForUser()->canChangeEmail()) {
            throw new HttpException(500, 'Change E-Mail is not allowed');
        }

        $model = new AccountChangeEmail();

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->sendChangeEmail()) {
            return $this->render('changeEmail_success', ['model' => $model]);
        }

        return $this->render('changeEmail', ['model' => $model]);
    }

    /**
     * After the user validated his email
     *
     */
    public function actionChangeEmailValidate()
    {
        if (!UserSourceService::getForUser()->canChangeEmail()) {
            throw new HttpException(500, 'Change E-Mail is not allowed');
        }

        $token = Yii::$app->request->get('token');
        $email = Yii::$app->request->get('email');

        $user = Yii::$app->user->getIdentity();

        // Check if Token is valid
        if (md5(Yii::$app->settings->get('secret') . $user->guid . $email) != $token) {
            throw new HttpException(404, Yii::t('UserModule.account', 'Invalid link! Please make sure that you entered the entire url.'));
        }

        // Check if E-Mail is in use, e.g. by other user
        $emailAvailablyCheck = User::findOne(['email' => $email]);
        if ($emailAvailablyCheck != null) {
            throw new HttpException(404, Yii::t('UserModule.account', 'The entered e-mail address is already in use by another user.'));
        }

        $user->email = $email;
        $user->save();

        return $this->render('changeEmailValidate', ['newEmail' => $email]);
    }

    /**
     * @deprecated since 1.20, moved to [[SecurityController::actionLogin()]]
     */
    public function actionChangePassword()
    {
        return $this->redirect(['/user/security/login'], 301);
    }

    /**
     * Returns the current user of this account
     *
     * An administration can also pass a user id via GET parameter to change users
     * accounts settings.
     *
     * @return User the user
     * @throws HttpException
     * @throws Throwable
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
