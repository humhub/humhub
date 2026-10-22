<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\components\psr\HttpFactory;
use humhub\components\psr\ResponseBridge;
use humhub\components\psr\ServerRequestFactory;
use humhub\modules\oauth\components\AuthorizationServerFactory;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\entities\ClientEntity;
use humhub\modules\oauth\entities\ScopeEntity;
use humhub\modules\oauth\entities\UserEntity;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\notifications\GrantCreatedNotification;
use humhub\modules\oauth\repositories\ClientRepository;
use humhub\modules\oauth\services\GrantService;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * The authorization endpoint: a regular web route, so login (any method), gates and consent
 * run in the browser before a code is issued.
 *
 * @since 1.20
 */
class AuthorizeController extends Controller
{
    private const SESSION_KEY = 'oauth.authRequests';

    /**
     * Pending consent requests kept per session, the oldest are dropped.
     */
    private const MAX_PENDING_REQUESTS = 5;

    /**
     * @inheritdoc
     */
    public $layout = '@humhub/modules/user/views/layouts/main';

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [[ControllerAccess::RULE_LOGGED_IN_ONLY]];
    }

    public function actionIndex()
    {
        if (Yii::$app->user->getImpersonation()->isActive()) {
            throw new ForbiddenHttpException(Yii::t('OauthModule.base', 'Apps cannot be authorized while impersonating a user.'));
        }

        $server = AuthorizationServerFactory::createAuthorizationServer();

        if (Yii::$app->request->isPost) {
            return $this->decide($server);
        }

        try {
            $authRequest = $server->validateAuthorizationRequest(ServerRequestFactory::fromYii(Yii::$app->request));
        } catch (OAuthServerException $e) {
            return $this->errorResponse($e);
        }

        // Only S256: league would also accept `plain`, which is the default without a method
        if ($authRequest->getCodeChallenge() !== null && $authRequest->getCodeChallengeMethod() !== 'S256') {
            return $this->redirectWithError($authRequest, 'invalid_request');
        }

        /** @var ClientEntity $clientEntity */
        $clientEntity = $authRequest->getClient();
        $client = $clientEntity->getRecord();

        if ($client->type === Client::TYPE_USER && (int)$client->owner_user_id !== (int)Yii::$app->user->id) {
            throw new ForbiddenHttpException(Yii::t('OauthModule.base', 'This app can only be used by the person who created it.'));
        }

        $scopeIds = $this->grantableScopes($authRequest, $client);
        if ($scopeIds === []) {
            return $this->redirectWithError($authRequest, 'invalid_scope');
        }
        $authRequest->setScopes(array_map(static fn(string $id) => new ScopeEntity($id), $scopeIds));

        if ($client->type === Client::TYPE_ADMIN && $client->skip_consent) {
            return $this->complete($server, $authRequest, true);
        }

        $requestId = Yii::$app->security->generateRandomString(32);
        $pending = $this->pendingRequests();
        $pending[$requestId] = ['userId' => (int)Yii::$app->user->id, 'request' => $authRequest];
        Yii::$app->session->set(self::SESSION_KEY, array_slice($pending, -self::MAX_PENDING_REQUESTS, null, true));

        return $this->render('index', [
            'client' => $client,
            'scopes' => array_map([ScopeRegistry::class, 'get'], $scopeIds),
            'redirectHost' => $this->describeRedirect($this->redirectUri($authRequest)),
            'requestId' => $requestId,
        ]);
    }

    private function decide(AuthorizationServer $server): Response
    {
        $requestId = Yii::$app->request->post('request');
        $pending = $this->pendingRequests();
        $stored = is_string($requestId) ? ($pending[$requestId] ?? null) : null;
        if ($stored !== null) {
            unset($pending[$requestId]);
            Yii::$app->session->set(self::SESSION_KEY, $pending);
        }

        $authRequest = is_array($stored) ? ($stored['request'] ?? null) : null;
        if (!$authRequest instanceof AuthorizationRequestInterface || (int)($stored['userId'] ?? 0) !== (int)Yii::$app->user->id) {
            throw new BadRequestHttpException(Yii::t('OauthModule.base', 'The authorization request has expired. Please start again.'));
        }

        return $this->complete($server, $authRequest, Yii::$app->request->post('decision') === 'allow');
    }

    private function complete(AuthorizationServer $server, AuthorizationRequestInterface $authRequest, bool $approved): Response
    {
        $userEntity = new UserEntity('');

        if ($approved) {
            /** @var ClientEntity $clientEntity */
            $clientEntity = $authRequest->getClient();

            // The client may have been disabled or changed since the request was validated
            $client = (new ClientRepository())->findUsableClient($clientEntity->getIdentifier());
            if ($client === null
                || ($client->type === Client::TYPE_USER && (int)$client->owner_user_id !== (int)Yii::$app->user->id)) {
                return $this->redirectWithError($authRequest, 'access_denied');
            }

            $grant = (new GrantService())->createOAuthGrant(
                Yii::$app->user->getIdentity(),
                $client,
                array_map(static fn($scope) => $scope->getIdentifier(), $authRequest->getScopes()),
            );
            $userEntity = new UserEntity($grant->guid);

            // Signing in to the app is the user's own routine, anything else is worth a notice
            if ($client->type !== Client::TYPE_SYSTEM) {
                GrantCreatedNotification::send(
                    Yii::$app->user->getIdentity(),
                    payload: ['type' => 'oauth', 'name' => $client->name],
                    dedupe: false,
                );
            }
        }

        $authRequest->setUser($userEntity);
        $authRequest->setAuthorizationApproved($approved);

        $response = HttpFactory::instance()->createResponse();
        try {
            $response = $server->completeAuthorizationRequest($authRequest, $response);
        } catch (OAuthServerException $e) {
            $response = $e->generateHttpResponse($response);
        }

        return ResponseBridge::toYii($response, Yii::$app->response);
    }

    private function pendingRequests(): array
    {
        $pending = Yii::$app->session->get(self::SESSION_KEY);
        return is_array($pending) ? $pending : [];
    }

    /**
     * Errors go back to the client only via a verified redirect URI, the others are shown here.
     *
     * @throws BadRequestHttpException
     */
    private function errorResponse(OAuthServerException $e): Response
    {
        if (!$e->hasRedirect()) {
            throw new BadRequestHttpException($e->getHint() ?? $e->getMessage(), 0, $e);
        }

        return ResponseBridge::toYii($e->generateHttpResponse(HttpFactory::instance()->createResponse()), Yii::$app->response);
    }

    /**
     * @return string[] the requested scopes reduced to what the client may get; `full` for the app
     */
    private function grantableScopes(AuthorizationRequestInterface $authRequest, Client $client): array
    {
        $allowed = (new GrantService())->getAllowedScopes($client);
        if ($client->type === Client::TYPE_SYSTEM) {
            return $allowed;
        }

        $requested = array_map(static fn($scope) => $scope->getIdentifier(), $authRequest->getScopes());
        return array_values(array_intersect($requested, $allowed));
    }

    private function redirectWithError(AuthorizationRequestInterface $authRequest, string $error): Response
    {
        $uri = $this->redirectUri($authRequest);
        $query = http_build_query(array_filter(
            ['error' => $error, 'state' => $authRequest->getState()],
            static fn($value) => $value !== null && $value !== '',
        ));

        return ResponseBridge::toYii(
            HttpFactory::instance()->createResponse(302)->withHeader('Location', $uri . (str_contains($uri, '?') ? '&' : '?') . $query),
            Yii::$app->response,
        );
    }

    /**
     * league allows omitting the redirect URI when the client has exactly one registered
     */
    private function redirectUri(AuthorizationRequestInterface $authRequest): string
    {
        if ($authRequest->getRedirectUri() !== null) {
            return (string)$authRequest->getRedirectUri();
        }

        $registered = $authRequest->getClient()->getRedirectUri();
        return (string)(is_array($registered) ? reset($registered) : $registered);
    }

    private function describeRedirect(string $uri): string
    {
        $parts = parse_url($uri);
        return $parts['host'] ?? (($parts['scheme'] ?? '') . ':');
    }
}
