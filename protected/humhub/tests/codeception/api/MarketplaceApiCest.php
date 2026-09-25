<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\marketplace\components\OnlineModuleManager;
use humhub\tests\codeception\unit\modules\marketplace\MarketplaceListServiceTest;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The marketplace API (`humhub\modules\marketplace\controllers\api\*`) and the module enabling
 * of the admin module — the endpoints the `MarketplaceBrowser` island talks to.
 *
 * humhub.com is never contacted: the catalogue, the categories and the latest core version are
 * seeded into the caches the marketplace reads. Nothing here installs or updates for real
 * (that would download); those endpoints are covered on their idempotent and refusing paths.
 *
 * One identity per test, as in the other API Cests.
 */
class MarketplaceApiCest
{
    public function _before(ApiTester $I): void
    {
        Yii::$app->cache->set(OnlineModuleManager::CACHE_KEY_MODULES, MarketplaceListServiceTest::catalogue());
        Yii::$app->cache->set(OnlineModuleManager::CACHE_KEY_CATEGORIES, [
            'categories' => [
                ['id' => 1, 'name' => 'Productivity', 'count' => 2],
                ['id' => 2, 'name' => 'Communication', 'count' => 1],
            ],
            'uncategorized' => 4,
        ]);
        Yii::$app->cache->set('latestVersion', '99.0.0');
        Yii::$app->getModule('marketplace')->settings->delete('includeCommunityModules');
        Yii::$app->getModule('marketplace')->settings->delete('includeBetaUpdates');
    }

    public function _after(ApiTester $I): void
    {
        Yii::$app->cache->delete(OnlineModuleManager::CACHE_KEY_MODULES);
        Yii::$app->cache->delete(OnlineModuleManager::CACHE_KEY_CATEGORIES);
        Yii::$app->cache->delete('latestVersion');
        Yii::$app->getModule('marketplace')->settings->delete('includeCommunityModules');
        Yii::$app->getModule('marketplace')->settings->delete('includeBetaUpdates');
    }

    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    public function testListsModulesUpdatesFirst(ApiTester $I)
    {
        $I->wantTo('list the marketplace modules, updates first');
        $I->amLoggedInAs(1);

        $I->sendGet('marketplace/module');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['total' => 7, 'page' => 1, 'pages' => 1, 'updateCount' => 1]);
        Assert::assertSame(
            ['friendship', 'calendar-x', 'polls-x', 'pro-x', 'paid-x', 'bought-x', 'like'],
            $I->grabDataFromResponseByJsonPath('$.results[*].id'),
        );
    }

    public function testFiltersTheList(ApiTester $I)
    {
        $I->wantTo('filter the module list by keyword, category, status and tag');
        $I->amLoggedInAs(1);

        $I->sendGet('marketplace/module', ['q' => 'events']);
        Assert::assertSame(['calendar-x'], $I->grabDataFromResponseByJsonPath('$.results[*].id'));

        $I->sendGet('marketplace/module', ['categoryId' => 2]);
        Assert::assertSame(['polls-x'], $I->grabDataFromResponseByJsonPath('$.results[*].id'));

        $I->sendGet('marketplace/module', ['status' => 'update,installed']);
        Assert::assertSame(['friendship', 'like'], $I->grabDataFromResponseByJsonPath('$.results[*].id'));

        $I->sendGet('marketplace/module', ['tag' => ['featured', 'partner']]);
        Assert::assertSame(['calendar-x', 'polls-x'], $I->grabDataFromResponseByJsonPath('$.results[*].id'));

        $I->sendGet('marketplace/module', ['id' => 'like', 'status' => 'notInstalled']);
        Assert::assertSame(['like'], $I->grabDataFromResponseByJsonPath('$.results[*].id'));

        $I->sendGet('marketplace/module', ['useCase' => 'intranet']);
        Assert::assertSame(['calendar-x', 'polls-x'], $I->grabDataFromResponseByJsonPath('$.results[*].id'));
    }

    public function testFiltersByTheCommunityTag(ApiTester $I)
    {
        $I->wantTo('filter the module list by the community tag');
        $I->amLoggedInAs(1);
        // Set before the first request: OnlineModuleManager memoises the catalogue per
        // instance, and this instance lives for the whole test once a request built it.
        Yii::$app->getModule('marketplace')->settings->set('includeCommunityModules', true);

        $I->sendGet('marketplace/module', ['tag' => 'community']);
        Assert::assertSame(['community-x'], $I->grabDataFromResponseByJsonPath('$.results[*].id'));
    }

    public function testPagesTheList(ApiTester $I)
    {
        $I->wantTo('read the second page of the module list');
        $I->amLoggedInAs(1);

        $I->sendGet('marketplace/module', ['page' => 2, 'pageSize' => 3]);

        $I->seeResponseContainsJson(['total' => 7, 'page' => 2, 'pageSize' => 3, 'pages' => 3]);
        Assert::assertSame(['pro-x', 'paid-x', 'bought-x'], $I->grabDataFromResponseByJsonPath('$.results[*].id'));
    }

    public function testRejectsUnknownFilterValues(ApiTester $I)
    {
        $I->wantTo('get a validation error for an unknown status');
        $I->amLoggedInAs(1);

        $I->sendGet('marketplace/module', ['status' => 'broken']);

        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.status');
    }

    public function testRejectsUnknownParameters(ApiTester $I)
    {
        $I->wantTo('get a validation error for a parameter the list does not know');
        $I->amLoggedInAs(1);

        $I->sendGet('marketplace/module', ['keyword' => 'events', 'pageSize' => 100]);

        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.keyword');
        $I->dontSeeResponseJsonMatchesJsonPath('$.errors.pageSize');
    }

    public function testRejectsANonIntegerCategoryId(ApiTester $I)
    {
        $I->wantTo('get a validation error for a categoryId that is not an integer');
        $I->amLoggedInAs(1);

        $I->sendGet('marketplace/module', ['categoryId' => 'not-a-number']);

        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.categoryId');
    }

    public function testRejectsAnInvalidUseCase(ApiTester $I)
    {
        $I->wantTo('get a validation error for a use case that is not a lowercase id');
        $I->amLoggedInAs(1);

        $I->sendGet('marketplace/module', ['useCase' => 'Bad Value!']);

        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.useCase');
    }

    public function testDescribesAvailability(ApiTester $I)
    {
        $I->wantTo('see what installing each module takes');
        $I->amLoggedInAs(1);

        $I->sendGet('marketplace/module', ['status' => 'notInstalled']);

        $I->seeResponseContainsJson(['results' => [
            ['id' => 'calendar-x', 'availability' => 'install', 'badge' => 'official', 'featured' => true],
            ['id' => 'pro-x', 'availability' => 'professionalEdition'],
            ['id' => 'paid-x', 'availability' => 'buy', 'price' => ['currency' => 'EUR', 'onRequest' => false]],
            ['id' => 'bought-x', 'availability' => 'install', 'purchased' => true],
        ]]);
    }

    public function testAnswers503WithoutACatalogue(ApiTester $I)
    {
        $I->wantTo('get 503 while humhub.com cannot be reached');
        $I->amLoggedInAs(1);
        Yii::$app->cache->set(OnlineModuleManager::CACHE_KEY_MODULES, []);

        $I->sendGet('marketplace/module');

        $I->seeResponseCodeIs(503);
    }

    public function testIsHiddenWhileTheMarketplaceIsDisabled(ApiTester $I)
    {
        $I->wantTo('get 404 while the marketplace is disabled');
        $I->amLoggedInAs(1);
        Yii::$app->getModule('marketplace')->enabled = false;

        try {
            $I->sendGet('marketplace/module');
            $I->seeResponseCodeIs(404);
        } finally {
            Yii::$app->getModule('marketplace')->enabled = true;
        }
    }

    public function testRequiresTheManageModulesPermission(ApiTester $I)
    {
        $I->wantTo('be refused the module list as a normal user');
        $I->amLoggedInAs(2);

        $I->sendGet('marketplace/module');

        $I->seeResponseCodeIs(403);
    }

    public function testRequiresAuthentication(ApiTester $I)
    {
        $I->wantTo('be refused the module list as a guest');

        $I->sendGet('marketplace/module');

        $I->seeResponseCodeIs(401);
    }

    public function testInstallingAnInstalledModuleIsIdempotent(ApiTester $I)
    {
        $I->wantTo('install a module that is installed already');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('marketplace/module/like/install');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => 'like', 'isEnabled' => true]);
        Assert::assertNotNull($I->grabDataFromResponseByJsonPath('$.installedVersion')[0]);
    }

    public function testInstallingWithoutACsrfTokenIsRefused(ApiTester $I)
    {
        $I->wantTo('be refused installing a module without a CSRF token');
        $I->amLoggedInAs(1);

        $I->sendPost('marketplace/module/like/install');

        $I->seeResponseCodeIs(403);
    }

    public function testRefusesToInstallWhatCannotBeInstalled(ApiTester $I)
    {
        $I->wantTo('be refused the installation of a Professional Edition module on a community licence');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('marketplace/module/pro-x/install');

        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.id');
    }

    public function testInstallingAnUnknownModuleIsNotFound(ApiTester $I)
    {
        $I->wantTo('get 404 installing a module the marketplace does not know');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('marketplace/module/nope-x/install');

        $I->seeResponseCodeIs(404);
    }

    public function testUpdatingWithoutAnUpdateIsIdempotent(ApiTester $I)
    {
        $I->wantTo('update a module that is up to date');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('marketplace/module/like/update');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => 'like', 'updateAvailable' => false]);
    }

    public function testUpdatingANotInstalledModuleIsRefused(ApiTester $I)
    {
        $I->wantTo('be refused the update of a module that is not installed');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('marketplace/module/calendar-x/update');

        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.id');
    }

    public function testListsCategories(ApiTester $I)
    {
        $I->wantTo('list the categories with their module counts');
        $I->amLoggedInAs(1);

        $I->sendGet('marketplace/category');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['results' => [
            ['id' => 1, 'name' => 'Productivity', 'count' => 2],
            ['id' => 2, 'name' => 'Communication', 'count' => 1],
            ['id' => -1, 'count' => 4],
        ]]);
    }

    public function testListsUseCases(ApiTester $I)
    {
        $I->wantTo('list the use cases with their module counts');
        $I->amLoggedInAs(1);

        $I->sendGet('marketplace/use-case');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['results' => [
            ['id' => 'education', 'name' => 'Education', 'count' => 2],
            ['id' => 'higher-education', 'name' => 'Higher education', 'count' => 1],
            ['id' => 'intranet', 'name' => 'Intranet', 'count' => 2],
        ]]);
    }

    public function testUseCasesAnswer503WithoutACatalogue(ApiTester $I)
    {
        $I->wantTo('get 503 for use cases while humhub.com cannot be reached');
        $I->amLoggedInAs(1);
        Yii::$app->cache->set(OnlineModuleManager::CACHE_KEY_MODULES, []);

        $I->sendGet('marketplace/use-case');

        $I->seeResponseCodeIs(503);
    }

    public function testDescribesTheCoreVersion(ApiTester $I)
    {
        $I->wantTo('learn whether a newer HumHub version exists');
        $I->amLoggedInAs(1);

        $I->sendGet('marketplace/core-version');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['installed' => Yii::$app->version, 'latest' => '99.0.0', 'updateAvailable' => true]);
        $I->seeResponseJsonMatchesJsonPath('$.updateUrl');
    }

    public function testReadsAndChangesTheSettings(ApiTester $I)
    {
        $I->wantTo('read and change the marketplace settings');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendGet('marketplace/settings');
        $I->seeResponseContainsJson(['includeBetaUpdates' => false, 'includeCommunityModules' => false]);

        $I->sendPatch('marketplace/settings', ['includeCommunityModules' => 1]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['includeBetaUpdates' => false, 'includeCommunityModules' => true]);
        Assert::assertTrue((bool)Yii::$app->getModule('marketplace')->settings->get('includeCommunityModules'));
    }

    public function testRejectsANonBooleanSetting(ApiTester $I)
    {
        $I->wantTo('get a validation error for a setting that is not a boolean');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPatch('marketplace/settings', ['includeBetaUpdates' => 'maybe']);

        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.includeBetaUpdates');
    }

    public function testRegisteringALicenceKeyNeedsAKey(ApiTester $I)
    {
        $I->wantTo('get a validation error registering an empty licence key');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('marketplace/licence-key', ['licenceKey' => '']);

        $I->seeResponseCodeIs(422);
        $I->seeResponseJsonMatchesJsonPath('$.errors.licenceKey');
    }

    public function testActivatingAnEnabledModuleIsIdempotent(ApiTester $I)
    {
        $I->wantTo('enable a module that is enabled already');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('module/like/enable');

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['id' => 'like', 'isEnabled' => true]);
    }

    public function testActivatingAnUnknownModuleIsNotFound(ApiTester $I)
    {
        $I->wantTo('get 404 enabling a module that is not installed');
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendPost('module/nope-x/enable');

        $I->seeResponseCodeIs(404);
    }

    public function testActivationRequiresTheManageModulesPermission(ApiTester $I)
    {
        $I->wantTo('be refused enabling a module as a normal user');
        $I->amLoggedInAs(2);
        $this->withCsrf($I);

        $I->sendPost('module/like/enable');

        $I->seeResponseCodeIs(403);
    }
}
