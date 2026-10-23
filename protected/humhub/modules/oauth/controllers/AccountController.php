<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\controllers;

use humhub\components\access\ControllerAccess;
use humhub\modules\oauth\components\CredentialActions;
use humhub\modules\oauth\models\forms\PersonalAccessTokenForm;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\widgets\AppsSections;
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
            [ControllerAccess::RULE_POST => ['revoke']],
        ]);
    }

    public function init()
    {
        $this->setActionTitles([
            'index' => Yii::t('OauthModule.base', 'Apps & Tokens'),
            'create-token' => Yii::t('OauthModule.base', 'Create token'),
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

        if ($action->id === 'create-token') {
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
}
