<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\oauth\components\TokenResolver;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\tests\codeception\_support\OAuthTestHelper;
use humhub\modules\user\models\User;
use Yii;

/**
 * Bearer tokens of the OAuth server on `/api/v2`: authentication and scopes.
 */
class OAuthApiAuthCest
{
    public function _before(ApiTester $I)
    {
        Module::instance()->getKeyStore()->generateIfMissing();
    }

    /**
     * The api suite does not roll back: the caller deletes the client (its grants cascade).
     */
    private function adminClient(array $scopes): Client
    {
        Yii::$app->getModule('oauth')->settings->set('allowClients', true);
        $client = new Client([
            'client_id' => 'agent-' . bin2hex(random_bytes(4)), 'type' => Client::TYPE_ADMIN, 'name' => 'Agent',
            'redirect_uris' => json_encode([OAuthTestHelper::REDIRECT_URI]),
            'scopes' => json_encode($scopes), 'is_active' => 1, 'created_at' => time(),
        ]);
        $client->save(false);
        return $client;
    }

    public function testAppTokenReachesTheApiAsItsUser(ApiTester $I)
    {
        $tokens = OAuthTestHelper::issueTokens(2);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $tokens['access_token']);
        $I->sendGet('account');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => 2]);
    }

    public function testTokenWritesNeedNoCsrf(ApiTester $I)
    {
        $tokens = OAuthTestHelper::issueTokens(1);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $tokens['access_token']);
        $I->sendPost('notification/mark-as-seen');
        $I->seeResponseCodeIs(200);
    }

    public function testScopesLimitAnAdminClient(ApiTester $I)
    {
        $client = $this->adminClient(['content:read']);
        try {
            $tokens = OAuthTestHelper::issueTokens(1, $client->client_id, ['content:read']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $tokens['access_token']);

            $I->sendGet('content/1/comments');
            $I->seeResponseCodeIs(200);

            $I->sendGet('account');
            $I->seeResponseCodeIs(403);
            $I->seeResponseContainsJson(['error' => 'insufficient_scope', 'scope' => 'account:read']);
            $I->seeHttpHeader('WWW-Authenticate', 'Bearer error="insufficient_scope", scope="account:read"');

            // endpoints without a declared scope need `full`
            $I->sendGet('marketplace/module');
            $I->seeResponseCodeIs(403);
            $I->seeResponseContainsJson(['scope' => 'full']);
        } finally {
            $client->delete();
        }
    }

    public function testRevokedGrantAndDisabledUserFail(ApiTester $I)
    {
        $tokens = OAuthTestHelper::issueTokens(2);
        TokenResolver::resolveGrant($tokens['refresh_token'])->delete();

        $I->haveHttpHeader('Authorization', 'Bearer ' . $tokens['access_token']);
        $I->sendGet('account');
        $I->seeResponseCodeIs(401);

        // the api suite does not roll back, so the user is enabled again afterwards
        $tokens = OAuthTestHelper::issueTokens(3);
        $user = User::findOne(['id' => 3]);
        $user->updateAttributes(['status' => User::STATUS_DISABLED]);
        try {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $tokens['access_token']);
            $I->sendGet('account');
            $I->seeResponseCodeIs(401);
        } finally {
            $user->updateAttributes(['status' => User::STATUS_ENABLED]);
        }
    }

    public function testInvalidTokenDoesNotFallBackToGuestAccess(ApiTester $I)
    {
        $tokens = OAuthTestHelper::issueTokens(2);
        TokenResolver::resolveGrant($tokens['refresh_token'])->delete();

        Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 1);
        try {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $tokens['access_token']);
            $I->sendGet('content/1/comments');
            $I->seeResponseCodeIs(401);
            $I->seeResponseIsJson();
            $I->seeHttpHeader('WWW-Authenticate', 'Bearer realm="api"');
        } finally {
            Yii::$app->getModule('user')->settings->set('auth.allowGuestAccess', 0);
        }
    }

    public function testForeignAndForgedTokens(ApiTester $I)
    {
        // not ours (no hh- kid): falls through to the next method; core-only installation → 401
        $I->haveHttpHeader('Authorization', 'Bearer some-other-token');
        $I->sendGet('account');
        $I->seeResponseCodeIs(401);

        // ours but tampered
        $tokens = OAuthTestHelper::issueTokens(1);
        $I->haveHttpHeader('Authorization', 'Bearer ' . substr($tokens['access_token'], 0, -4) . 'AAAA');
        $I->sendGet('account');
        $I->seeResponseCodeIs(401);
        $I->seeHttpHeader('WWW-Authenticate', 'Bearer realm="api"');
    }
}
