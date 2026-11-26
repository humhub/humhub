<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\functional;

use FunctionalTester;
use Yii;

/**
 * The HTTP endpoints of the health check.
 *
 * @since 1.20
 */
class HealthCest
{
    private const TOKEN = 'health-test-token-0123456789abcdef';

    public function _before(FunctionalTester $I)
    {
        Yii::$app->health->token = self::TOKEN;
    }

    private function json(FunctionalTester $I): array
    {
        return json_decode($I->grabPageSource(), true, flags: JSON_THROW_ON_ERROR);
    }

    public function testReadyWithoutTokenIsMinimal(FunctionalTester $I)
    {
        $I->amOnRoute('/health/ready');

        $I->seeResponseCodeIs(200);
        $I->assertSame(['format', 'status', 'state'], array_keys($this->json($I)));
        $I->assertSame('installed', $this->json($I)['state']);
    }

    public function testLiveWithoutTokenIsMinimal(FunctionalTester $I)
    {
        $I->amOnRoute('/health/live');

        $I->seeResponseCodeIs(200);
        $I->assertSame(['format', 'status', 'state'], array_keys($this->json($I)));
    }

    public function testAllNeedsAToken(FunctionalTester $I)
    {
        $I->amOnRoute('/health/index');

        $I->seeResponseCodeIs(401);
    }

    public function testDetailedWithToken(FunctionalTester $I)
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . self::TOKEN);
        $I->amOnRoute('/health/ready');

        $I->seeResponseCodeIs(200);
        $data = $this->json($I);
        $I->assertSame('ready', $data['level']);
        $I->assertArrayHasKey('database', $data['checks']);
        $I->assertSame(Yii::$app->version, $data['version']);
    }

    public function testAllWithToken(FunctionalTester $I)
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . self::TOKEN);
        $I->amOnRoute('/health/index');

        $I->seeResponseCodeIsBetween(200, 503);
        $I->assertSame('all', $this->json($I)['level']);
    }

    public function testAnInvalidTokenIsRejectedNotDowngraded(FunctionalTester $I)
    {
        $I->haveHttpHeader('Authorization', 'Bearer wrong-token-wrong-token-wrong-token');
        $I->amOnRoute('/health/ready');

        $I->seeResponseCodeIs(401);
    }

    public function testATooShortConfiguredTokenCountsAsNotConfigured(FunctionalTester $I)
    {
        Yii::$app->health->token = 'short';
        $I->haveHttpHeader('Authorization', 'Bearer short');
        $I->amOnRoute('/health/ready');

        $I->seeResponseCodeIs(401);
    }

    public function testRepeatedInvalidTokensAreRateLimited(FunctionalTester $I)
    {
        Yii::$app->cache->flush();
        $I->haveHttpHeader('Authorization', 'Bearer wrong-token-wrong-token-wrong-token');
        for ($i = 0; $i < 12; $i++) {
            $I->amOnRoute('/health/ready');
        }

        $I->seeResponseCodeIs(429);
        Yii::$app->cache->flush();
    }

    public function testHeadersAndNoSession(FunctionalTester $I)
    {
        $I->amOnRoute('/health/ready');

        $I->assertSame('no-store', $I->grabHttpHeader('Cache-Control'));
        $I->assertSame('noindex', $I->grabHttpHeader('X-Robots-Tag'));
        $I->assertFalse(Yii::$app->session->getIsActive());
        $I->assertSame(0, Yii::$app->response->cookies->count);
    }
}
