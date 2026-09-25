<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\marketplace;

use humhub\modules\marketplace\components\OnlineModuleManager;
use humhub\modules\marketplace\models\Module;
use humhub\modules\marketplace\serializers\MarketplaceModuleSerializer;
use humhub\modules\marketplace\services\MarketplaceListService;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The catalogue below stands in for humhub.com (seeded into the cache the manager reads).
 * `friendship` and `like` are core modules, always installed in tests: `friendship` has an
 * update (99.0.0), `like` does not (0.0.1).
 *
 * @since 1.20
 */
class MarketplaceListServiceTest extends HumHubDbTestCase
{
    public static function catalogue(): array
    {
        return [
            'calendar-x' => ['id' => 'calendar-x', 'name' => 'Calendar X', 'description' => 'Plan events together', 'latestVersion' => '2.0.0', 'latestCompatibleVersion' => '2.0.0', 'categories' => [1], 'featured' => true, 'isThirdParty' => false, 'marketplaceUrl' => 'https://marketplace.humhub.com/module/calendar-x', 'useCases' => 'Intranet, Education'],
            'polls-x' => ['id' => 'polls-x', 'name' => 'Polls X', 'description' => 'Ask questions', 'latestVersion' => '1.0.0', 'latestCompatibleVersion' => '1.0.0', 'categories' => [2], 'isThirdParty' => true, 'isPartner' => true, 'useCases' => 'intranet'],
            'pro-x' => ['id' => 'pro-x', 'name' => 'Pro X', 'description' => 'Enterprise feature', 'latestVersion' => '1.0.0', 'latestCompatibleVersion' => '1.0.0', 'categories' => [], 'professional_only' => true, 'isThirdParty' => false, 'useCases' => 'education'],
            'paid-x' => ['id' => 'paid-x', 'name' => 'Paid X', 'description' => 'Costs money', 'latestVersion' => '1.0.0', 'latestCompatibleVersion' => '1.0.0', 'categories' => [], 'isThirdParty' => true, 'price_eur' => '49', 'checkoutUrl' => 'https://www.humhub.com/checkout?returnTo=-returnToUrl-'],
            'bought-x' => ['id' => 'bought-x', 'name' => 'Bought X', 'description' => 'Already paid', 'latestVersion' => '1.0.0', 'latestCompatibleVersion' => '1.0.0', 'categories' => [], 'isThirdParty' => true, 'price_eur' => '49', 'purchased' => true, 'licence_key' => 'KEY-1', 'useCases' => 'higher-education'],
            'old-x' => ['id' => 'old-x', 'name' => 'Old X', 'description' => 'No compatible version', 'latestVersion' => '0.1.0', 'categories' => []],
            'community-x' => ['id' => 'community-x', 'name' => 'Community X', 'description' => 'From the community', 'latestVersion' => '1.0.0', 'latestCompatibleVersion' => '1.0.0', 'categories' => [], 'isThirdParty' => true, 'isCommunity' => true],
            'friendship' => ['id' => 'friendship', 'name' => 'Friendship', 'description' => 'Befriend users', 'latestVersion' => '99.0.0', 'latestCompatibleVersion' => '99.0.0', 'categories' => [1], 'isThirdParty' => false],
            'like' => ['id' => 'like', 'name' => 'Like', 'description' => 'Like content', 'latestVersion' => '0.0.1', 'latestCompatibleVersion' => '0.0.1', 'categories' => [], 'isThirdParty' => false],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        Yii::$app->cache->set(OnlineModuleManager::CACHE_KEY_MODULES, self::catalogue());
        Yii::$app->getModule('marketplace')->settings->delete('includeCommunityModules');
    }

    protected function tearDown(): void
    {
        Yii::$app->cache->delete(OnlineModuleManager::CACHE_KEY_MODULES);
        Yii::$app->getModule('marketplace')->settings->delete('includeCommunityModules');
        parent::tearDown();
    }

    private function service(): MarketplaceListService
    {
        return new MarketplaceListService(new OnlineModuleManager());
    }

    private function ids(array $modules): array
    {
        return array_map(static fn(Module $module) => $module->id, $modules);
    }

    public function testSortsUpdatesFirstThenNotInstalledThenInstalled(): void
    {
        $this->assertSame(
            ['friendship', 'calendar-x', 'polls-x', 'pro-x', 'paid-x', 'bought-x', 'like'],
            $this->ids($this->service()->find()),
        );
    }

    public function testFiltersByStatus(): void
    {
        $this->assertSame(['friendship'], $this->ids($this->service()->find(['status' => ['update']])));
        $this->assertSame(['like'], $this->ids($this->service()->find(['status' => ['installed']])));
        $this->assertSame(['friendship', 'like'], $this->ids($this->service()->find(['status' => ['update', 'installed']])));
    }

    public function testFiltersByKeyword(): void
    {
        $this->assertSame(['calendar-x'], $this->ids($this->service()->find(['q' => 'events'])));
    }

    public function testFiltersByCategory(): void
    {
        $this->assertSame(['friendship', 'calendar-x'], $this->ids($this->service()->find(['categoryId' => 1])));
        $this->assertSame(['pro-x', 'paid-x', 'bought-x', 'like'], $this->ids($this->service()->find(['categoryId' => -1])));
    }

    public function testFiltersByAnyOfTheTags(): void
    {
        $this->assertSame(['calendar-x'], $this->ids($this->service()->find(['tag' => ['featured']])));
        $this->assertSame(['polls-x', 'bought-x'], $this->ids($this->service()->find(['tag' => ['partner', 'purchased']])));
    }

    public function testFiltersByTheCommunityTag(): void
    {
        Yii::$app->getModule('marketplace')->settings->set('includeCommunityModules', true);

        $this->assertSame(['community-x'], $this->ids($this->service()->find(['tag' => ['community']])));
    }

    public function testFiltersByAnyOfTheUseCases(): void
    {
        $this->assertSame(['calendar-x', 'polls-x'], $this->ids($this->service()->find(['useCase' => ['intranet']])));
        $this->assertSame(['calendar-x', 'pro-x'], $this->ids($this->service()->find(['useCase' => ['education']])));
        $this->assertSame(['bought-x'], $this->ids($this->service()->find(['useCase' => ['higher-education']])));
        $this->assertSame(
            ['calendar-x', 'polls-x', 'pro-x'],
            $this->ids($this->service()->find(['useCase' => ['intranet', 'education']])),
        );
    }

    public function testCountsUseCases(): void
    {
        $this->assertSame([
            ['id' => 'education', 'name' => 'Education', 'count' => 2],
            ['id' => 'higher-education', 'name' => 'Higher education', 'count' => 1],
            ['id' => 'intranet', 'name' => 'Intranet', 'count' => 2],
        ], $this->service()->useCaseCounts());
    }

    public function testGetUseCaseList(): void
    {
        $module = new Module(['id' => 'x', 'useCases' => ' Intranet, EDUCATION ,,higher-education ']);

        $this->assertSame(['intranet', 'education', 'higher-education'], $module->getUseCaseList());
        $this->assertSame([], (new Module(['id' => 'y']))->getUseCaseList());
    }

    public function testIdSelectsOneModuleRegardlessOfTheOtherFilters(): void
    {
        $this->assertSame(['like'], $this->ids($this->service()->find(['id' => 'like', 'status' => ['notInstalled']])));
        $this->assertSame([], $this->ids($this->service()->find(['id' => 'unknown'])));
    }

    public function testCommunityModulesOnlyWithTheSetting(): void
    {
        $this->assertNotContains('community-x', $this->ids($this->service()->find()));

        Yii::$app->getModule('marketplace')->settings->set('includeCommunityModules', true);
        $this->assertContains('community-x', $this->ids($this->service()->find()));
    }

    public function testCountsAvailableUpdates(): void
    {
        $this->assertSame(1, $this->service()->updateCount());
    }

    public function testIsUnavailableWithAnEmptyCatalogue(): void
    {
        Yii::$app->cache->set(OnlineModuleManager::CACHE_KEY_MODULES, []);
        $this->assertFalse($this->service()->isAvailable());
    }

    public function testSerializesTheWireShape(): void
    {
        $modules = [];
        foreach ($this->service()->find() as $module) {
            $modules[$module->id] = MarketplaceModuleSerializer::module($module);
        }

        $this->assertSame('install', $modules['calendar-x']['availability']);
        $this->assertSame('official', $modules['calendar-x']['badge']);
        $this->assertTrue($modules['calendar-x']['featured']);
        $this->assertNull($modules['calendar-x']['installedVersion']);
        $this->assertSame([1], $modules['calendar-x']['categories']);
        $this->assertSame(['intranet', 'education'], $modules['calendar-x']['useCases']);

        $this->assertFalse($modules['polls-x']['featured']);

        $this->assertSame('professionalEdition', $modules['pro-x']['availability']);
        $this->assertSame('professional', $modules['pro-x']['badge']);

        $this->assertSame('buy', $modules['paid-x']['availability']);
        $this->assertSame(['amount' => 49.0, 'currency' => 'EUR', 'onRequest' => false], $modules['paid-x']['price']);
        // The console UrlManager used in this test app has no pretty-URL rules, so the embedded
        // route comes back as "r=marketplace%2Fbrowse"; decode before asserting on it.
        $this->assertStringContainsString('marketplace/browse', urldecode($modules['paid-x']['checkoutUrl']));
        $this->assertStringContainsString('tag=purchased', urldecode($modules['paid-x']['checkoutUrl']));

        $this->assertSame('install', $modules['bought-x']['availability']);
        $this->assertSame('KEY-1', $modules['bought-x']['licenceKey']);

        $this->assertTrue($modules['friendship']['updateAvailable']);
        $this->assertNotNull($modules['friendship']['installedVersion']);
        $this->assertTrue($modules['like']['isEnabled']);
        $this->assertFalse($modules['like']['updateAvailable']);
    }
}
