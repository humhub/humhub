<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\access\ControllerAccess;
use humhub\modules\space\helpers\MembershipHelper;
use humhub\modules\user\components\BaseAccountController;
use humhub\modules\user\models\forms\AccountDelete;
use humhub\modules\user\models\forms\PrivacySettings;
use humhub\modules\user\models\Password;
use humhub\modules\user\services\UserSessionService;
use humhub\modules\user\services\UserSourceService;
use humhub\modules\user\widgets\security\ProfilePermissionsSection;
use humhub\modules\user\widgets\security\SecuritySessionSections;
use humhub\modules\user\widgets\SecuritySettingsMenu;
use Yii;
use yii\web\HttpException;

/**
 * Security & Privacy settings of the current user's account
 *
 * @since 1.20
 */
class SecurityController extends BaseAccountController
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return array_merge(parent::getAccessRules(), [
            [ControllerAccess::RULE_POST => ['disconnect', 'terminate-session', 'terminate-other-sessions']],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setActionTitles([
            'login' => Yii::t('UserModule.account', 'Login'),
            'sessions' => Yii::t('UserModule.account', 'Devices & Access'),
            'privacy' => Yii::t('UserModule.account', 'Privacy'),
            'data' => Yii::t('UserModule.account', 'My Data'),
            'delete' => Yii::t('UserModule.account', 'Delete account'),
        ]);

        parent::init();
    }

    public function actionIndex()
    {
        $url = (new SecuritySettingsMenu())->getFirstUrl();
        if ($url === null) {
            throw new HttpException(404);
        }

        return $this->redirect($url);
    }

    public function actionLogin()
    {
        $model = null;

        if (UserSourceService::getForUser()->canChangePassword()) {
            $model = new Password(['scenario' => 'changePassword']);

            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $model->user_id = Yii::$app->user->id;
                $model->setPassword($model->newPassword);
                if ($model->save()) {
                    // Saving the password already invalidated the other sessions, drop their rows as well
                    $sessions = new UserSessionService($this->getUser());
                    if ($sessions->isAvailable()) {
                        $sessions->deleteOthers();
                    }

                    $this->view->success(Yii::t('UserModule.account', 'Your password has been successfully changed!'));
                    return $this->redirect(['login']);
                }
            }
        }

        return $this->render('login', ['passwordModel' => $model]);
    }

    public function actionDisconnect(string $authclient)
    {
        foreach (Yii::$app->user->getAuthClientUserService()->getClients() as $authClient) {
            if ($authClient->getId() === $authclient) {
                Yii::$app->user->getAuthClientUserService()->remove($authClient);
            }
        }

        return $this->redirect(['login']);
    }

    public function actionSessions()
    {
        if (!(new SecuritySessionSections())->hasVisibleSections()) {
            throw new HttpException(404);
        }

        return $this->render('sessions');
    }

    public function actionTerminateSession(string $key)
    {
        if ($this->getSessionService()->terminate($key)) {
            $this->view->success(Yii::t('UserModule.account', 'The session has been logged out.'));
        }

        return $this->redirect(['sessions']);
    }

    public function actionTerminateOtherSessions()
    {
        $this->getSessionService()->terminateOthers();
        $this->view->success(Yii::t('UserModule.account', 'All other sessions have been logged out.'));

        return $this->redirect(['sessions']);
    }

    public function actionPrivacy()
    {
        $permissions = new ProfilePermissionsSection();
        if ($permissions->isVisible()) {
            // The permission grid posts its changes to the current URL
            $response = $this->getUser()->permissionManager->handlePermissionStateChange($permissions->getCurrentGroup());
            if ($response !== null) {
                return $response;
            }
        }

        $model = new PrivacySettings(['user' => $this->getUser()]);
        if ($model->hasFields() && $model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
            return $this->redirect(['privacy']);
        }

        return $this->render('privacy', ['privacyModel' => $model]);
    }

    public function actionData()
    {
        return $this->render('data');
    }

    public function actionDelete()
    {
        if (!UserSourceService::getForUser()->canDeleteAccount()) {
            throw new HttpException(403, 'Account deletion not allowed!');
        }

        $ownSpaces = MembershipHelper::getOwnSpaces($this->getUser());
        if (count($ownSpaces) !== 0) {
            return $this->render('delete_spaceowner', ['ownSpaces' => $ownSpaces]);
        }

        $model = new AccountDelete(['user' => $this->getUser()]);
        if ($model->load(Yii::$app->request->post()) && $model->performDelete()) {
            Yii::$app->user->logout();
            return $this->goHome();
        }

        return $this->render('delete', ['model' => $model]);
    }

    private function getSessionService(): UserSessionService
    {
        $service = new UserSessionService($this->getUser());
        if (!$service->isAvailable()) {
            throw new HttpException(404);
        }

        return $service;
    }
}
