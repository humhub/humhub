<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\controllers;

use humhub\components\access\ControllerAccess;
use humhub\modules\oauth\components\CredentialActions;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\forms\ClientForm;
use humhub\modules\oauth\models\forms\PersonalAccessTokenForm;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\widgets\AppsSections;
use humhub\modules\oauth\widgets\OwnClientsSection;
use humhub\modules\oauth\widgets\TokensSection;
use humhub\modules\user\components\BaseAccountController;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * The "Apps & Tokens" tab of the account's security settings.
 *
 * @since 1.20
 */
class AccountController extends BaseAccountController
{
    use CredentialActions;

    protected function getAccessRules()
    {
        return array_merge(parent::getAccessRules(), [
            [ControllerAccess::RULE_POST => ['revoke', 'delete-client', 'regenerate-secret']],
        ]);
    }

    public function init()
    {
        $this->setActionTitles([
            'index' => Yii::t('OauthModule.base', 'Apps & Tokens'),
            'create-token' => Yii::t('OauthModule.base', 'Create token'),
            'client' => Yii::t('OauthModule.base', 'OAuth app'),
            'regenerate-secret' => Yii::t('OauthModule.base', 'OAuth app'),
        ]);

        parent::init();
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!AppsSections::isAvailable()) {
            throw new NotFoundHttpException();
        }

        // Saving a client may create its secret
        if (in_array($action->id, ['create-token', 'regenerate-secret'], true)
            || ($action->id === 'client' && Yii::$app->request->isPost)) {
            $this->denyWhileImpersonating();
        }

        return true;
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionRevoke(int $id)
    {
        $grant = Grant::findOne(['id' => $id, 'user_id' => $this->getUser()->id]);
        if ($grant === null) {
            throw new NotFoundHttpException();
        }

        (new GrantService())->revoke($grant);
        $this->view->success(Yii::t('OauthModule.base', 'Access revoked.'));

        // Without the last grant the tab may be gone
        return $this->redirect(AppsSections::isAvailable(true) ? ['index'] : ['/user/security/index']);
    }

    public function actionCreateToken()
    {
        if (!TokensSection::canCreate()) {
            throw new ForbiddenHttpException();
        }

        $model = new PersonalAccessTokenForm($this->getUser());
        if ($model->load(Yii::$app->request->post()) && $model->create()) {
            // Rendered, not redirected to: the token must not pass through the session or any cache
            Yii::$app->response->headers->set('Cache-Control', 'no-store');
            return $this->render('token-created', ['token' => $model->getToken(), 'name' => $model->name]);
        }

        return $this->render('create-token', ['model' => $model]);
    }

    public function actionClient(?int $id = null)
    {
        $this->ensureCanManageClients();

        $model = new ClientForm($id === null ? new Client() : $this->findOwnClient($id), $this->getUser(), [
            'scenario' => ClientForm::SCENARIO_USER,
        ]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->getSecret() !== null) {
                return $this->renderSecret($model->client, $model->getSecret());
            }

            $this->view->saved();
            return $this->redirect(['index']);
        }

        return $this->render('client', ['model' => $model]);
    }

    public function actionDeleteClient(int $id)
    {
        $this->ensureCanManageClients();

        // Its grants go with it (foreign key)
        $this->findOwnClient($id)->delete();
        $this->view->success(Yii::t('OauthModule.base', 'The app has been deleted.'));

        return $this->redirect(['index']);
    }

    public function actionRegenerateSecret(int $id)
    {
        $this->ensureCanManageClients();

        return $this->regenerateSecret($this->findOwnClient($id));
    }

    private function ensureCanManageClients(): void
    {
        if (!OwnClientsSection::canManage()) {
            throw new ForbiddenHttpException();
        }
    }

    private function findOwnClient(int $id): Client
    {
        $client = Client::findOne(['id' => $id, 'type' => Client::TYPE_USER, 'owner_user_id' => $this->getUser()->id]);
        if ($client === null) {
            throw new NotFoundHttpException();
        }

        return $client;
    }
}
