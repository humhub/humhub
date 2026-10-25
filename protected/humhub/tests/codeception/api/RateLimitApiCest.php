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
use humhub\modules\oauth\tests\codeception\_support\OAuthTestHelper;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * Rate limiting of `/api/v2` by channel and scope (`ApiRateLimitFilter`).
 *
 * The suite starts a new application per test and runs every request of a test in it, so a
 * policy set on `Yii::$app->rateLimiter` before the requests applies to all of them and is
 * gone in the next test. The suite config counts in memory (`MemoryRateLimitStorage`), so
 * every test starts with empty counters.
 *
 * @since 1.20
 */
class RateLimitApiCest
{
    private const TWO_PER_MINUTE = ['burst' => ['limit' => 2, 'span' => 60]];

    public function _before(ApiTester $I)
    {
        Module::instance()->getKeyStore()->generateIfMissing();
    }

    private function setPolicy(string $name, array|false $windows): void
    {
        Yii::$app->rateLimiter->policies[$name] = $windows;
    }

    public function testSessionReadsAreLimited(ApiTester $I)
    {
        $this->setPolicy('session.*:read', self::TWO_PER_MINUTE);
        $I->amLoggedInAs(1);

        $I->sendGet('account');
        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('RateLimit-Policy', '"burst";q=2;w=60');
        Assert::assertMatchesRegularExpression('/^"burst";r=1;t=\d+$/', $I->grabHttpHeader('RateLimit'));

        $I->sendGet('account');
        $I->seeResponseCodeIs(200);
        Assert::assertMatchesRegularExpression('/^"burst";r=0;t=\d+$/', $I->grabHttpHeader('RateLimit'));

        $I->sendGet('account');
        $I->seeResponseCodeIs(429);
        $retryAfter = (int)$I->grabHttpHeader('Retry-After');
        Assert::assertGreaterThan(0, $retryAfter);
        $I->seeResponseContainsJson([
            'error' => 'rate_limited',
            'message' => "Too many requests, try again in $retryAfter seconds.",
            'retryAfter' => $retryAfter,
        ]);
    }

    public function testTokensHaveTheirOwnPolicyAndABudgetPerGrant(ApiTester $I)
    {
        // `account` declares `account:read`, whatever the token holds
        $this->setPolicy('token.*:read', self::TWO_PER_MINUTE);
        $first = OAuthTestHelper::issueTokens(1);
        $second = OAuthTestHelper::issueTokens(1);
        // a token wins over the session, which is used once the header is gone
        $I->amLoggedInAs(1);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $first['access_token']);
        $I->sendGet('account');
        $I->sendGet('account');
        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('RateLimit-Policy', '"burst";q=2;w=60');
        $I->sendGet('account');
        $I->seeResponseCodeIs(429);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $second['access_token']);
        $I->sendGet('account');
        $I->seeResponseCodeIs(200);

        // the same user's session is counted apart from its tokens
        $I->deleteHeader('Authorization');
        $I->sendGet('account');
        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('RateLimit-Policy', '"burst";q=600;w=60, "long";q=10000;w=3600');
    }

    public function testWritesUseTheWritePolicy(ApiTester $I)
    {
        // token writes need no CSRF token
        $this->setPolicy('token.*:write', self::TWO_PER_MINUTE);
        $tokens = OAuthTestHelper::issueTokens(1);
        $I->haveHttpHeader('Authorization', 'Bearer ' . $tokens['access_token']);

        $I->sendPost('notification/mark-as-seen');
        $I->sendPost('notification/mark-as-seen');
        $I->seeResponseCodeIs(200);
        $I->sendPost('notification/mark-as-seen');
        $I->seeResponseCodeIs(429);

        // reads keep their own budget
        $I->sendGet('notification');
        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('RateLimit-Policy', '"burst";q=300;w=60, "long";q=5000;w=3600');
    }

    public function testRequestsRefusedByScopeAreNotCounted(ApiTester $I)
    {
        $this->setPolicy('token.*:read', self::TWO_PER_MINUTE);

        $settings = Yii::$app->getModule('oauth')->settings;
        $allowClients = $settings->get('allowClients');
        $settings->set('allowClients', true);
        $client = new Client([
            'client_id' => 'agent-' . bin2hex(random_bytes(4)), 'type' => Client::TYPE_ADMIN, 'name' => 'Agent',
            'redirect_uris' => json_encode([OAuthTestHelper::REDIRECT_URI]),
            'scopes' => json_encode(['content:read']), 'is_active' => 1, 'created_at' => time(),
        ]);
        $client->save(false);

        try {
            $tokens = OAuthTestHelper::issueTokens(1, $client->client_id, ['content:read']);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $tokens['access_token']);

            // account:read is not granted: refused by the scope filter, under the same policy
            for ($i = 0; $i < 3; $i++) {
                $I->sendGet('account');
                $I->seeResponseCodeIs(403);
                $I->dontSeeHttpHeader('RateLimit');
            }

            $I->sendGet('content/1/comments');
            $I->seeResponseCodeIs(200);
            $I->sendGet('content/1/comments');
            $I->seeResponseCodeIs(200);
            $I->sendGet('content/1/comments');
            $I->seeResponseCodeIs(429);
        } finally {
            $client->delete();
            $settings->set('allowClients', $allowClients);
        }
    }

    public function testDisabledPolicyLeavesRequestsUnlimited(ApiTester $I)
    {
        $this->setPolicy('session.*:read', false);
        $this->setPolicy('session.full', self::TWO_PER_MINUTE);
        $I->amLoggedInAs(1);

        for ($i = 0; $i < 3; $i++) {
            $I->sendGet('account');
            $I->seeResponseCodeIs(200);
            $I->dontSeeHttpHeader('RateLimit');
        }
    }
}
