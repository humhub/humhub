<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\_support;

use humhub\components\psr\HttpFactory;
use humhub\modules\oauth\components\AuthorizationServerFactory;
use humhub\modules\oauth\entities\UserEntity;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\user\models\User;
use League\OAuth2\Server\Exception\OAuthServerException;

/**
 * Runs the authorization step in-process, so tests can start at the token endpoint.
 */
class OAuthTestHelper
{
    public const REDIRECT_URI = 'com.humhub.app:/oauth/callback';

    public static function challenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    /**
     * @return string the authorization code
     */
    public static function issueAuthorizationCode(
        int $userId,
        string $verifier,
        string $clientId = Client::SYSTEM_CLIENT_ID,
        string $redirectUri = self::REDIRECT_URI,
        array $scopes = ['full'],
    ): string {
        Module::instance()->getKeyStore()->generateIfMissing();

        $server = AuthorizationServerFactory::createAuthorizationServer();
        $request = HttpFactory::instance()->createServerRequest('GET', 'http://localhost/oauth/authorize')
            ->withQueryParams([
                'response_type' => 'code',
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'state' => 'test-state',
                'scope' => implode(' ', $scopes),
                'code_challenge' => self::challenge($verifier),
                'code_challenge_method' => 'S256',
            ]);

        $authRequest = $server->validateAuthorizationRequest($request);
        $grant = (new GrantService())->createOAuthGrant(User::findOne(['id' => $userId]), Client::findByClientId($clientId), $scopes);
        $authRequest->setUser(new UserEntity($grant->guid));
        $authRequest->setAuthorizationApproved(true);

        $response = $server->completeAuthorizationRequest($authRequest, HttpFactory::instance()->createResponse());
        parse_str((string)parse_url($response->getHeaderLine('Location'), PHP_URL_QUERY), $query);

        return $query['code'];
    }

    /**
     * @return array{access_token: string, refresh_token: string} the token response
     */
    public static function issueTokens(int $userId, string $clientId = Client::SYSTEM_CLIENT_ID, array $scopes = ['full']): array
    {
        Module::instance()->getKeyStore()->generateIfMissing();

        $verifier = bin2hex(random_bytes(32));
        $code = self::issueAuthorizationCode($userId, $verifier, $clientId, self::REDIRECT_URI, $scopes);

        $request = HttpFactory::instance()->createServerRequest('POST', 'http://localhost/oauth/token')
            ->withParsedBody([
                'grant_type' => 'authorization_code',
                'client_id' => $clientId,
                'redirect_uri' => self::REDIRECT_URI,
                'code' => $code,
                'code_verifier' => $verifier,
            ]);

        $response = AuthorizationServerFactory::createAuthorizationServer()
            ->respondToAccessTokenRequest($request, HttpFactory::instance()->createResponse());

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @return array the decoded token response, an error body on failure
     */
    public static function refresh(string $refreshToken, string $clientId = Client::SYSTEM_CLIENT_ID): array
    {
        $request = HttpFactory::instance()->createServerRequest('POST', 'http://localhost/oauth/token')
            ->withParsedBody([
                'grant_type' => 'refresh_token',
                'client_id' => $clientId,
                'refresh_token' => $refreshToken,
            ]);

        try {
            $response = AuthorizationServerFactory::createAuthorizationServer()
                ->respondToAccessTokenRequest($request, HttpFactory::instance()->createResponse());
        } catch (OAuthServerException $e) {
            $response = $e->generateHttpResponse(HttpFactory::instance()->createResponse());
        }

        return json_decode((string)$response->getBody(), true);
    }
}
