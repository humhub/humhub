<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\tests\codeception\_support\OAuthTestHelper;
use PHPUnit\Framework\Assert;

/**
 * The token, revocation and metadata endpoints of the OAuth server.
 */
class OAuthTokenEndpointCest
{
    private const BASE = 'http://localhost:8080/';

    public function _before(ApiTester $I)
    {
        Module::instance()->getKeyStore()->generateIfMissing();
    }

    public function testMetadata(ApiTester $I)
    {
        $I->sendGet(self::BASE . '.well-known/oauth-authorization-server');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'code_challenge_methods_supported' => ['S256'],
        ]);
        $token = $I->grabDataFromResponseByJsonPath('$.token_endpoint')[0];
        $issuer = $I->grabDataFromResponseByJsonPath('$.issuer')[0];
        Assert::assertStringEndsWith('/oauth/token', $token);
        Assert::assertSame(parse_url($issuer, PHP_URL_SCHEME) . '://' . parse_url($issuer, PHP_URL_HOST), parse_url($token, PHP_URL_SCHEME) . '://' . parse_url($token, PHP_URL_HOST));
        $I->seeResponseContainsJson(['revocation_endpoint_auth_methods_supported' => ['none', 'client_secret_basic', 'client_secret_post']]);
    }

    public function testCodeExchangeAndRefreshWithRotation(ApiTester $I)
    {
        $verifier = bin2hex(random_bytes(32));
        $code = OAuthTestHelper::issueAuthorizationCode(1, $verifier);

        $I->haveHttpHeader('User-Agent', 'HumHubApp/2.0 (iOS 18; iPhone15,2)');
        $I->sendPost(self::BASE . 'oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => 'humhub-app',
            'redirect_uri' => OAuthTestHelper::REDIRECT_URI,
            'code' => $code,
            'code_verifier' => $verifier,
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['token_type' => 'Bearer']);
        $refresh = $I->grabDataFromResponseByJsonPath('$.refresh_token')[0];

        $grant = Grant::find()->where(['user_id' => 1])->orderBy(['id' => SORT_DESC])->one();
        Assert::assertSame('HumHubApp/2.0 (iOS 18; iPhone15,2)', $grant->user_agent);

        $I->sendPost(self::BASE . 'oauth/token', ['grant_type' => 'refresh_token', 'client_id' => 'humhub-app', 'refresh_token' => $refresh]);
        $I->seeResponseCodeIs(200);

        // the code is single use; its reuse revokes the grant
        $I->sendPost(self::BASE . 'oauth/token', [
            'grant_type' => 'authorization_code', 'client_id' => 'humhub-app',
            'redirect_uri' => OAuthTestHelper::REDIRECT_URI, 'code' => $code, 'code_verifier' => $verifier,
        ]);
        $I->seeResponseCodeIs(400);
    }

    public function testWrongVerifierIsRejected(ApiTester $I)
    {
        $code = OAuthTestHelper::issueAuthorizationCode(1, bin2hex(random_bytes(32)));

        $I->sendPost(self::BASE . 'oauth/token', [
            'grant_type' => 'authorization_code', 'client_id' => 'humhub-app',
            'redirect_uri' => OAuthTestHelper::REDIRECT_URI, 'code' => $code, 'code_verifier' => str_repeat('a', 64),
        ]);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson(['error' => 'invalid_grant']);
    }

    public function testRefreshTokenReuseRevokesTheGrant(ApiTester $I)
    {
        $tokens = OAuthTestHelper::issueTokens(1);

        $I->sendPost(self::BASE . 'oauth/token', ['grant_type' => 'refresh_token', 'client_id' => 'humhub-app', 'refresh_token' => $tokens['refresh_token']]);
        $I->seeResponseCodeIs(200);
        $newRefresh = $I->grabDataFromResponseByJsonPath('$.refresh_token')[0];

        // the old one again: theft — the grant is revoked, the new one dies with it
        $I->sendPost(self::BASE . 'oauth/token', ['grant_type' => 'refresh_token', 'client_id' => 'humhub-app', 'refresh_token' => $tokens['refresh_token']]);
        $I->seeResponseCodeIs(400);

        $I->sendPost(self::BASE . 'oauth/token', ['grant_type' => 'refresh_token', 'client_id' => 'humhub-app', 'refresh_token' => $newRefresh]);
        $I->seeResponseCodeIs(400);
    }

    public function testRevokeDeletesTheGrant(ApiTester $I)
    {
        $tokens = OAuthTestHelper::issueTokens(1);
        $grantCount = Grant::find()->count();

        $I->sendPost(self::BASE . 'oauth/revoke', ['token' => $tokens['refresh_token'], 'client_id' => 'humhub-app']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame($grantCount - 1, (int)Grant::find()->count());

        // unknown or already revoked: still 200 (RFC 7009)
        $I->sendPost(self::BASE . 'oauth/revoke', ['token' => $tokens['refresh_token'], 'client_id' => 'humhub-app']);
        $I->seeResponseCodeIs(200);

        $I->sendPost(self::BASE . 'oauth/token', ['grant_type' => 'refresh_token', 'client_id' => 'humhub-app', 'refresh_token' => $tokens['refresh_token']]);
        $I->seeResponseCodeIs(400);
    }

    public function testRevokeRequiresAValidClient(ApiTester $I)
    {
        $tokens = OAuthTestHelper::issueTokens(1);

        $I->sendPost(self::BASE . 'oauth/revoke', ['token' => $tokens['refresh_token'], 'client_id' => 'unknown']);
        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson(['error' => 'invalid_client']);
    }

    public function testRevokeByAnotherClientLeavesTheGrant(ApiTester $I)
    {
        Module::instance()->settings->set('allowClients', true);
        // the api suite does not roll back, so the client gets a unique id and is removed afterwards
        $clientId = 'other-' . bin2hex(random_bytes(4));
        $client = new Client([
            'client_id' => $clientId, 'type' => Client::TYPE_ADMIN, 'name' => 'Other',
            'secret_hash' => hash('sha256', 's3cret'), 'redirect_uris' => '[]', 'scopes' => '["full"]', 'is_active' => 1,
        ]);
        Assert::assertTrue($client->save(false));

        try {
            $tokens = OAuthTestHelper::issueTokens(1);
            $grantCount = Grant::find()->count();

            $I->sendPost(self::BASE . 'oauth/revoke', [
                'token' => $tokens['refresh_token'], 'client_id' => $clientId, 'client_secret' => 's3cret',
            ]);
            $I->seeResponseCodeIs(200);
            Assert::assertSame($grantCount, (int)Grant::find()->count());
        } finally {
            $client->delete();
        }
    }

    public function testRevokeByAccessToken(ApiTester $I)
    {
        $tokens = OAuthTestHelper::issueTokens(1);
        $grantCount = Grant::find()->count();

        $I->sendPost(self::BASE . 'oauth/revoke', ['token' => $tokens['access_token'], 'client_id' => 'humhub-app']);
        $I->seeResponseCodeIs(200);
        Assert::assertSame($grantCount - 1, (int)Grant::find()->count());
    }

    public function testTokenResponseIsNotCacheable(ApiTester $I)
    {
        $tokens = OAuthTestHelper::issueTokens(1);

        $I->sendPost(self::BASE . 'oauth/token', ['grant_type' => 'refresh_token', 'client_id' => 'humhub-app', 'refresh_token' => $tokens['refresh_token']]);
        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('Cache-Control', 'no-store');
    }

    public function testCodeReuseRevokesTheGrant(ApiTester $I)
    {
        $verifier = bin2hex(random_bytes(32));
        $code = OAuthTestHelper::issueAuthorizationCode(1, $verifier);
        $params = [
            'grant_type' => 'authorization_code', 'client_id' => 'humhub-app',
            'redirect_uri' => OAuthTestHelper::REDIRECT_URI, 'code' => $code, 'code_verifier' => $verifier,
        ];

        $I->sendPost(self::BASE . 'oauth/token', $params);
        $I->seeResponseCodeIs(200);
        $refresh = $I->grabDataFromResponseByJsonPath('$.refresh_token')[0];

        $I->sendPost(self::BASE . 'oauth/token', $params);
        $I->seeResponseCodeIs(400);

        $I->sendPost(self::BASE . 'oauth/token', ['grant_type' => 'refresh_token', 'client_id' => 'humhub-app', 'refresh_token' => $refresh]);
        $I->seeResponseCodeIs(400);
    }

    public function testRevokeWithoutTokenIsAnInvalidRequest(ApiTester $I)
    {
        $I->sendPost(self::BASE . 'oauth/revoke', ['client_id' => 'humhub-app']);
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson(['error' => 'invalid_request']);
    }
}
