<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\modules\marketplace;

use humhub\modules\marketplace\components\OnlineModuleManager;
use humhub\modules\marketplace\Module;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * @since 1.19
 */
class OnlineModuleManagerTest extends HumHubDbTestCase
{
    private const CACHE_KEY = 'onlineModuleManager_modules';

    private const COMMUNITY_INSTALLED_ID = 'content';   // core module, always installed in tests
    private const COMMUNITY_NOT_INSTALLED_ID = 'fake_community_module_for_test';
    private const REGULAR_NOT_INSTALLED_ID = 'fake_regular_module_for_test';

    /**
     * @var Module
     */
    private $marketplaceModule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->marketplaceModule = Yii::$app->getModule('marketplace');

        Yii::$app->cache->set(self::CACHE_KEY, [
            self::COMMUNITY_INSTALLED_ID => [
                'name' => 'Installed community module',
                'isCommunity' => true,
            ],
            self::COMMUNITY_NOT_INSTALLED_ID => [
                'name' => 'Uninstalled community module',
                'isCommunity' => true,
            ],
            self::REGULAR_NOT_INSTALLED_ID => [
                'name' => 'Uninstalled regular module',
                'isCommunity' => false,
            ],
        ]);
    }

    protected function tearDown(): void
    {
        Yii::$app->cache->delete(self::CACHE_KEY);
        $this->marketplaceModule->settings->delete('includeCommunityModules');

        parent::tearDown();
    }

    public function testCommunityModulesAreHiddenByDefault(): void
    {
        $this->marketplaceModule->settings->set('includeCommunityModules', false);

        $modules = (new OnlineModuleManager())->getModules();

        $this->assertArrayHasKey(self::REGULAR_NOT_INSTALLED_ID, $modules);
        $this->assertArrayNotHasKey(self::COMMUNITY_NOT_INSTALLED_ID, $modules);
    }

    public function testCommunityModulesAreShownWhenSettingEnabled(): void
    {
        $this->marketplaceModule->settings->set('includeCommunityModules', true);

        $modules = (new OnlineModuleManager())->getModules();

        $this->assertArrayHasKey(self::REGULAR_NOT_INSTALLED_ID, $modules);
        $this->assertArrayHasKey(self::COMMUNITY_NOT_INSTALLED_ID, $modules);
    }

    public function testInstalledCommunityModulesAreAlwaysVisible(): void
    {
        $this->marketplaceModule->settings->set('includeCommunityModules', false);

        $this->assertTrue(
            Yii::$app->moduleManager->hasModule(self::COMMUNITY_INSTALLED_ID),
            'Precondition: ' . self::COMMUNITY_INSTALLED_ID . ' is expected to be installed in the test environment',
        );

        $modules = (new OnlineModuleManager())->getModules();

        $this->assertArrayHasKey(self::COMMUNITY_INSTALLED_ID, $modules);
    }
}
