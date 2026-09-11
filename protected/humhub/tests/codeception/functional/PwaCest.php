<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\functional;

use FunctionalTester;
use humhub\services\PwaService;
use Yii;

/**
 * Verifies the Progressive Web App endpoints. All three are reachable for guests
 * independently of the guest mode setting — a browser fetches them without a session.
 *
 * @since 1.20
 */
class PwaCest
{
    public function testManifestIsServedToGuests(FunctionalTester $I)
    {
        $I->wantTo('ensure the web app manifest is served to guests');

        $I->amOnRoute(PwaService::ROUTE_MANIFEST);

        $I->seeResponseCodeIs(200);

        $manifest = json_decode($I->grabPageSource(), true);

        $I->assertIsArray($manifest);
        $I->assertArrayHasKey('icons', $manifest);
        $I->assertSame('standalone', $manifest['display']);
        $I->assertSame(Yii::$app->name, $manifest['name']);
    }

    public function testServiceWorkerIsServedAsJavaScript(FunctionalTester $I)
    {
        $I->wantTo('ensure the service worker is served as JavaScript');

        $I->amOnRoute(PwaService::ROUTE_SERVICE_WORKER);

        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('Content-Type', 'application/javascript');
        $I->seeInSource('OFFLINE_PAGE_URL');
    }

    public function testOfflinePageIsServedToGuests(FunctionalTester $I)
    {
        $I->wantTo('ensure the offline fallback page is served to guests');

        $I->amOnRoute(PwaService::ROUTE_OFFLINE);

        $I->seeResponseCodeIs(200);
        $I->see('Unable to connect to');
    }
}
