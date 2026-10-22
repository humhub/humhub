<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\WebSessionService;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;

/**
 * Issues the app a single-use code that turns its grant into a web session.
 *
 * Token-only on purpose: `full` is required as an explicit scope, and only grants of the
 * HumHub app get a code.
 *
 * @since 1.20
 */
class WebSessionController extends BaseController
{
    /**
     * @inheritdoc
     */
    protected array $scopes = ['*' => ScopeRegistry::FULL];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['create' => ['POST']]],
        ]);
    }

    public function actionCreate(): array
    {
        $grant = Module::instance()->activeGrant;
        if ($grant === null) {
            throw new ForbiddenHttpException(Yii::t('OauthModule.base', 'Only the HumHub app can open web sessions.'));
        }

        try {
            $credentials = (new WebSessionService())->createCode($grant);
        } catch (\yii\base\InvalidArgumentException) {
            throw new ForbiddenHttpException(Yii::t('OauthModule.base', 'Only the HumHub app can open web sessions.'));
        }

        // The response carries a secret
        Yii::$app->response->headers->set('Cache-Control', 'no-store');

        return [
            'url' => Module::instance()->getEndpointUrl(['/oauth/web-session/index', 'code' => $credentials['code']]),
            'headers' => [WebSessionService::SECRET_HEADER => $credentials['secret']],
            'expiresIn' => WebSessionService::CODE_LIFETIME,
        ];
    }
}
