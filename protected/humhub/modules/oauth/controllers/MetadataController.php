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

        $module = Module::instance();

        return [
            'issuer' => $module->getIssuer(),
            'authorization_endpoint' => $module->getEndpointUrl(['/oauth/authorize/index']),
            'token_endpoint' => $module->getEndpointUrl(['/oauth/token/token']),
            'revocation_endpoint' => $module->getEndpointUrl(['/oauth/token/revoke']),
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'code_challenge_methods_supported' => ['S256'],
            'token_endpoint_auth_methods_supported' => ['none', 'client_secret_basic', 'client_secret_post'],
            'revocation_endpoint_auth_methods_supported' => ['none', 'client_secret_basic', 'client_secret_post'],
            'scopes_supported' => array_keys(ScopeRegistry::getScopes()),
        ];
    }
}
