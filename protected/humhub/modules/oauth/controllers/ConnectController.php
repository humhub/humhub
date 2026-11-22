<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\oauth\components\CredentialActions;
use humhub\modules\oauth\components\DeviceConnection;
use humhub\modules\oauth\components\DeviceConnectionRegistry;
use humhub\modules\oauth\models\forms\DeviceTokenForm;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\components\SudoFilter;
use humhub\modules\user\models\User;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * The dialog of a device connection a module offers (`DeviceConnection`): the values the device
 * asks for, and a personal access token as its password.
 *
 * @since 1.20
 */
class ConnectController extends Controller
{
    use CredentialActions;

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY],
            [ControllerAccess::RULE_POST => ['create']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        // A personal access token bypasses 2FA: its creation asks for the sudo mode
        return array_merge(parent::behaviors(), [
            'sudo' => ['class' => SudoFilter::class, 'only' => ['create']],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if ($action->id === 'create') {
            $this->denyWhileImpersonating();
        }

        return true;
    }

    public function actionIndex(string $id)
    {
        $connection = $this->findConnection($id);

        return $this->renderAjax('index', [
            'connection' => $connection,
            'model' => new DeviceTokenForm(['name' => $connection->tokenName]),
            'limitReached' => (new PersonalAccessTokenService())->hasReachedLimit($this->getUser()),
        ]);
    }

    public function actionCreate(string $id)
    {
        $connection = $this->findConnection($id);
        $service = new PersonalAccessTokenService();
        $model = new DeviceTokenForm(['name' => $connection->tokenName]);
        $limitReached = $service->hasReachedLimit($this->getUser());

        if (!$limitReached && $model->load(Yii::$app->request->post()) && $model->validate()) {
            [$token, $grant] = $service->issue($this->getUser(), $connection->id, $model->name, $connection->scopes);

            // Rendered, not redirected to: the token must not pass through the session or any cache
            Yii::$app->response->headers->set('Cache-Control', 'no-store');
            return $this->renderAjax('created', [
                'connection' => $connection,
                'token' => $token,
                'grant' => $grant,
            ]);
        }

        return $this->renderAjax('index', [
            'connection' => $connection,
            'model' => $model,
            'limitReached' => $limitReached,
        ]);
    }

    private function getUser(): User
    {
        return Yii::$app->user->getIdentity();
    }

    /**
     * @throws NotFoundHttpException for a connection no module offers the user, also while
     * personal access tokens are not allowed
     */
    private function findConnection(string $id): DeviceConnection
    {
        return DeviceConnectionRegistry::find($this->getUser(), $id)
            ?? throw new NotFoundHttpException(Yii::t('OauthModule.base', 'This connection is not available.'));
    }
}
