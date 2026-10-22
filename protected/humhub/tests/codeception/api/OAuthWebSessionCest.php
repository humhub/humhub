<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\services\WebSessionService;
use humhub\modules\oauth\tests\codeception\_support\OAuthTestHelper;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * `POST /api/v2/oauth/web-session`: the app's code for a WebView session.
 */
class OAuthWebSessionCest
{
    public function _before()
    {
        Module::instance()->getKeyStore()->generateIfMissing();
    }

    public function testAppTokenGetsASingleUseUrl(ApiTester $I)
    {
        $tokens = OAuthTestHelper::issueTokens(2);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $tokens['access_token']);
        $I->sendPost('oauth/web-session');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['expiresIn' => 60]);
        $I->seeHttpHeader('Cache-Control', 'no-store');
        $response = json_decode($I->grabResponse(), true);
        $url = $response['url'];
        Assert::assertMatchesRegularExpression('#/oauth/web-session\?code=[A-Za-z0-9_-]{43}$#', $url);
        $secret = $response['headers'][WebSessionService::SECRET_HEADER];
        Assert::assertMatchesRegularExpression('#^[A-Za-z0-9_-]{43}$#', $secret);
        Assert::assertStringNotContainsString($secret, $url);
    }

    public function testOtherClientsAndSessionsGetNoCode(ApiTester $I)
    {
        Module::instance()->settings->set('allowClients', true);
        $client = new Client([
            'client_id' => 'agent-' . bin2hex(random_bytes(4)), 'type' => Client::TYPE_ADMIN, 'name' => 'Agent',
            'redirect_uris' => json_encode([OAuthTestHelper::REDIRECT_URI]),
            'scopes' => json_encode(['full']), 'is_active' => 1, 'created_at' => time(),
        ]);
        $client->save(false);

        try {
            $tokens = OAuthTestHelper::issueTokens(1, $client->client_id, ['full']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $tokens['access_token']);
            $I->sendPost('oauth/web-session');
            $I->seeResponseCodeIs(403);
        } finally {
            $client->delete();
        }
    }

    public function testABoundSessionEndsWithItsGrant(ApiTester $I)
    {
        $grant = (new GrantService())->createOAuthGrant(User::findOne(['id' => 2]), Client::findByClientId(Client::SYSTEM_CLIENT_ID), ['full']);
        $I->amLoggedInAs(2);
        Yii::$app->session->set(WebSessionService::SESSION_KEY_GRANT, $grant->id);

        $I->sendGet('user/4/follow');
        $I->seeResponseCodeIs(200);

        (new GrantService())->revoke($grant);

        $I->sendGet('user/4/follow');
        $I->seeResponseCodeIs(401);
    }

    public function testSessionAuthenticationIsNotAccepted(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        $I->sendPost('oauth/web-session');
        $I->seeResponseCodeIs(401);
    }
}
