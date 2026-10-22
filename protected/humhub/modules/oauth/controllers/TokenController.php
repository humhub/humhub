<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\controllers;

use humhub\components\psr\HttpFactory;
use humhub\components\psr\ResponseBridge;
use humhub\components\psr\ServerRequestFactory;
use humhub\modules\oauth\components\AuthorizationServerFactory;
use humhub\modules\oauth\components\TokenResolver;
use humhub\modules\oauth\repositories\ClientRepository;
use humhub\modules\oauth\services\GrantService;
use League\OAuth2\Server\Exception\OAuthServerException;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * The token endpoint (code exchange, refresh) and token revocation (RFC 7009).
 *
 * A plain Yii controller on purpose: no session, no CSRF, no access control — the client
 * authenticates itself, and guest access settings do not apply.
 *
 * @since 1.20
 */
class TokenController extends Controller
{
    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['token' => ['POST'], 'revoke' => ['POST']],
            ],
        ];
    }

    public function actionToken(): Response
    {
        $response = HttpFactory::instance()->createResponse();

        try {
            $response = AuthorizationServerFactory::createAuthorizationServer()
                ->respondToAccessTokenRequest(ServerRequestFactory::fromYii(Yii::$app->request), $response);
        } catch (OAuthServerException $e) {
            $response = $e->generateHttpResponse($response);
        }

        return ResponseBridge::toYii($response, Yii::$app->response);
    }

    /**
     * Revokes the grant of an access or refresh token. Unknown tokens answer 200 as well.
     */
    public function actionRevoke(): Response
    {
        $request = Yii::$app->request;
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        [$basicId, $basicSecret] = $request->getAuthCredentials();
        $clientId = (string)($basicId ?? $request->post('client_id', ''));
        $secret = $basicSecret ?? $request->post('client_secret');

        $clients = new ClientRepository();
        if ($clientId === '' || !$clients->validateClient($clientId, $secret, null)) {
            $response->setStatusCode(401);
            $response->data = ['error' => 'invalid_client'];
            return $response;
        }

        $client = $clients->findUsableClient($clientId);
        $grant = TokenResolver::resolveGrant((string)$request->post('token', ''));
        if ($grant !== null && $grant->client_id === $client->id) {
            (new GrantService())->revoke($grant);
        }

        $response->data = [];
        return $response;
    }
}
