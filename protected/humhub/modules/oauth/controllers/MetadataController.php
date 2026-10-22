<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\controllers;

use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\Module;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

/**
 * Authorization server metadata (RFC 8414).
 *
 * @since 1.20
 */
class MetadataController extends Controller
{
    public function actionIndex(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'issuer' => Module::instance()->getIssuer(),
            'authorization_endpoint' => Url::to(['/oauth/authorize/index'], true),
            'token_endpoint' => Url::to(['/oauth/token/token'], true),
            'revocation_endpoint' => Url::to(['/oauth/token/revoke'], true),
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'code_challenge_methods_supported' => ['S256'],
            'token_endpoint_auth_methods_supported' => ['none', 'client_secret_basic', 'client_secret_post'],
            'scopes_supported' => array_keys(ScopeRegistry::getScopes()),
        ];
    }
}
