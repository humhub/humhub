<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\controllers;

use humhub\modules\admin\components\Controller;
use humhub\components\access\ControllerAccess;
use humhub\modules\oauth\components\CredentialActions;
use humhub\modules\admin\permissions\ManageSettings;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\forms\ClientForm;
use humhub\modules\oauth\models\forms\SettingsForm;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * "Apps & API Access" in the user settings of the administration.
 *
 * @since 1.20
 */
class AdminController extends Controller
{
    use CredentialActions;

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            ['permissions' => [ManageSettings::class]],
            [ControllerAccess::RULE_POST => ['delete-client', 'regenerate-secret']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->subLayout = '@admin/views/layouts/user';
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        // Saving a client may create its secret
        if ($action->id === 'regenerate-secret' || ($action->id === 'client' && Yii::$app->request->isPost)) {
            $this->denyWhileImpersonating();
        }

        return true;
    }

    public function actionIndex()
    {
        $model = new SettingsForm();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $this->view->saved();
            return $this->redirect(['index']);
        }

        return $this->render('index', ['model' => $model]);
    }

    /**
     * The clients (`oauth\widgets\AdminClientTable`, its rows from `GET /api/v2/admin/oauth-client`)
     */
    public function actionClients()
    {
        return $this->render('clients');
    }

    public function actionClient(?int $id = null)
    {
        $model = new ClientForm($id === null ? new Client() : $this->findClient($id), Yii::$app->user->getIdentity(), [
            'scenario' => ClientForm::SCENARIO_ADMIN,
        ]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($model->getSecret() !== null) {
                return $this->renderSecret($model->client, $model->getSecret());
            }

            $this->view->saved();
            return $this->redirect(['clients']);
        }

        return $this->render('client', ['model' => $model]);
    }

    public function actionDeleteClient(int $id)
    {
        // Its grants go with it (foreign key)
        $this->findClient($id)->delete();
        $this->view->success(Yii::t('OauthModule.base', 'The client has been deleted.'));

        return $this->redirect(['clients']);
    }

    public function actionRegenerateSecret(int $id)
    {
        $client = $this->findClient($id);
        if (!$client->isConfidential()) {
            throw new BadRequestHttpException();
        }

        $secret = $client->generateSecret();
        $client->save(false);

        return $this->renderSecret($client, $secret);
    }

    /**
     * The grants of all users (`oauth\widgets\AdminGrantTable`, its rows from
     * `GET /api/v2/admin/oauth-grant`, which also revokes them)
     */
    public function actionGrants()
    {
        return $this->render('grants');
    }

    /**
     * Rendered, not redirected to: the secret must not pass through the session or any cache.
     */
    private function renderSecret(Client $client, string $secret): string
    {
        Yii::$app->response->headers->set('Cache-Control', 'no-store');

        return $this->render('client-secret', ['client' => $client, 'secret' => $secret]);
    }

    private function findClient(int $id): Client
    {
        $client = Client::findOne(['id' => $id]);
        if ($client === null) {
            throw new NotFoundHttpException();
        }

        if ($client->type === Client::TYPE_SYSTEM) {
            throw new ForbiddenHttpException();
        }

        return $client;
    }
}
