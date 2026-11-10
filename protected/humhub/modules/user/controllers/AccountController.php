<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\compat\HForm;
use humhub\modules\content\widgets\ContainerTagPicker;
use humhub\modules\user\components\BaseAccountController;
use humhub\modules\user\components\SudoFilter;
use humhub\modules\user\models\forms\AccountSettings;
use humhub\modules\user\models\User;
use humhub\modules\user\services\UserSourceService;
use Throwable;
use Yii;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
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
            'edit-tags' => Yii::t('UserModule.base', 'Profile'),
            'edit-settings' => Yii::t('UserModule.account', 'Account'),
            'change-email-validate' => Yii::t('UserModule.account', 'Account'),
            'edit-modules' => Yii::t('UserModule.base', 'Modules'),
        ]);
        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            // Only a change of the username or the e-mail address asks for it, not one of the other settings
            'sudo' => [
                'class' => SudoFilter::class,
                'only' => ['edit-settings'],
                'when' => function () {
                    $model = $this->getSettingsForm();
                    return $model->load(Yii::$app->request->post()) && $model->changesCredentials();
                },
            ],
        ]);
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
     * Edit Users Profile, one category of the profile fields per page
     *
     * @param int|null $category the id of the category, the first one by default
     * @throws Throwable
     */
    public function actionEdit($category = null)
    {
        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $user->profile->scenario = 'editProfile';

        $categories = $user->profile->getFormCategories();
        $id = $category === null ? array_key_first($categories) : (int)$category;
        if (!isset($categories[$id])) {
            throw new NotFoundHttpException();
        }
        $fields = $categories[$id];
        unset($fields['title']); // The card is titled with the category

        $form = new HForm([
            'elements' => ['category_' . $id => $fields],
            'buttons' => [
                'save' => [
                    'type' => 'submit',
                    'label' => Yii::t('UserModule.account', 'Save profile'),
                    'class' => 'btn btn-primary',
                ],
            ],
        ], $user->profile);
        $form->showErrorSummary = true;
        // Only the fields of this category, so an empty required field of another one does not block saving
        $form->attributeNames = array_keys($fields['elements']);

        if ($form->submitted('save') && $form->validate() && $form->save()) {
            // Trigger search refresh
            $user->save();

            $this->view->saved();
            return $this->redirect(['edit', 'category' => $id]);
        }

        return $this->render('edit', ['hForm' => $form]);
    }

    /**
     * The tags of the profile, a page of the profile tabs
     */
    public function actionEditTags()
    {
        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $user->scenario = User::SCENARIO_EDIT_PROFILE;
        $user->tagsField = $user->getTags();

        if ($user->load(Yii::$app->request->post())) {
            // An emptied picker posts an empty string
            $user->tagsField = is_array($user->tagsField) ? $user->tagsField : [];
            if ($user->save()) {
                $this->view->saved();
                return $this->redirect(['edit-tags']);
            }
        }

        return $this->render('editTags', ['user' => $user]);
    }

    /**
     * The "General" tab of the account settings: username, e-mail address and the introduction tour in one form
     */
    public function actionEditSettings()
    {
        $model = $this->getSettingsForm();

        if (!$model->load(Yii::$app->request->post())) {
            return $this->render('editSettings', ['model' => $model]);
        }

        // Before saving: afterwards the new username is the current one
        $messages = [];
        if ($model->isUsernameChanged()) {
            $messages[] = Yii::t('UserModule.account', 'Your username has been successfully changed. <br> We´ve just sent an e-mail to you with new information.');
        }
        if ($model->isEmailChanged()) {
            $messages[] = Yii::t('UserModule.account', 'We´ve just sent an confirmation e-mail to your new address. <br /> Please follow the instructions inside.');
        }

        if (!$model->save()) {
            return $this->render('editSettings', ['model' => $model]);
        }

        $messages === [] ? $this->view->saved() : $this->view->success(implode('<br>', $messages));

        return $this->redirect(['edit-settings']);
    }

    private function getSettingsForm(): AccountSettings
    {
        return new AccountSettings(['user' => Yii::$app->user->getIdentity()]);
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

        $this->view->success(Yii::t('UserModule.account', 'Your e-mail address has been successfully changed to {email}.', ['{email}' => $email]));
        return $this->redirect(['edit-settings']);
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
