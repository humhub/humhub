<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\services;

use humhub\components\Module;
use humhub\components\ModuleManager;
use humhub\models\ModuleEnabled;
use humhub\services\ModuleDependencyService;
use humhub\services\ModuleService;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\ServerErrorHttpException;

/**
 * Module dependencies are declared in `module.json` under `humhub.modules` and checked
 * against metadata only — the `module_enabled` table and the `module.json` files on disk.
 *
 * Fixture modules in `_data/ModuleDependencies`:
 * - `dep-parent` 2.0.0
 * - `dep-other` 1.0.0
 * - `dep-child` 1.0.0, depends on `dep-parent` >= 2.1.0
 *
 * @see ModuleDependencyService
 * @since 1.19.1
 */
class ModuleDependencyServiceTest extends HumHubDbTestCase
{
    private const FIXTURE_MODULE_IDS = ['dep-parent', 'dep-other', 'dep-child'];

    private array $originalAutoloadPaths;
    private array $originalAliases;
    private ModuleManager $originalModuleManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalAliases = Yii::$aliases;
        $this->originalModuleManager = Yii::$app->moduleManager;
        $this->originalAutoloadPaths = Yii::$app->params['moduleAutoloadPaths'];
        Yii::$app->params['moduleAutoloadPaths'][] = $this->getFixturePath();

        $this->cleanUpFixtureModules();
    }

    protected function tearDown(): void
    {
        $this->cleanUpFixtureModules();

        Yii::$app->params['moduleAutoloadPaths'] = $this->originalAutoloadPaths;
        Yii::$app->set('moduleManager', $this->originalModuleManager);
        Yii::$aliases = $this->originalAliases;

        parent::tearDown();
    }

    public function testModuleWithoutDependencies(): void
    {
        $service = new ModuleDependencyService(['id' => 'example', 'humhub' => ['minVersion' => '1.19']]);

        static::assertSame([], $service->getDependencies());
        static::assertSame([], $service->getUnmetDependencies());
    }

    public function testReadsDependenciesFromModulePath(): void
    {
        $service = ModuleDependencyService::forModulePath($this->getFixturePath() . '/child');

        static::assertSame(['dep-parent' => ['minVersion' => '2.1.0']], $service->getDependencies());
    }

    public function testIgnoresMalformedDependencies(): void
    {
        $service = new ModuleDependencyService(['humhub' => ['modules' => [
            'dep-parent' => 'not-an-object',
            'dep-other' => ['minVersion' => ['1.0']],
            0 => ['minVersion' => '1.0'],
        ]]]);

        static::assertSame(['dep-parent' => [], 'dep-other' => []], $service->getDependencies());
        static::assertSame([], (new ModuleDependencyService(['humhub' => ['modules' => 'calendar']]))->getDependencies());
    }

    public function testDependencyNotInstalled(): void
    {
        $service = $this->createService(['not-installed' => []]);

        static::assertSame(
            ['You must first install and enable the not-installed module.'],
            $service->getUnmetDependencies(),
        );
    }

    public function testDependencyInstalledButNotEnabled(): void
    {
        $service = $this->createService(['dep-parent' => []]);

        static::assertSame(
            ['You must first install and enable the Dependency Parent module.'],
            $service->getUnmetDependencies(),
        );
    }

    public function testDependencyEnabled(): void
    {
        $this->enableModule('dep-parent');

        static::assertSame([], $this->createService(['dep-parent' => []])->getUnmetDependencies());
        static::assertSame([], $this->createService(['dep-parent' => ['minVersion' => '2.0.0']])->getUnmetDependencies());
    }

    public function testDependencyVersionTooLow(): void
    {
        $this->enableModule('dep-parent');

        static::assertSame(
            ['You must first upgrade the Dependency Parent module to version 2.1.0 or later.'],
            $this->createService(['dep-parent' => ['minVersion' => '2.1.0']])->getUnmetDependencies(),
        );
    }

    public function testPlannedVersionIsCheckedInsteadOfInstalledVersion(): void
    {
        $this->enableModule('dep-parent');
        $dependencies = ['dep-parent' => ['minVersion' => '2.1.0']];

        static::assertSame([], $this->createService($dependencies, ['dep-parent' => '2.1.0'])->getUnmetDependencies());
        static::assertCount(1, $this->createService($dependencies, ['dep-parent' => '2.0.5'])->getUnmetDependencies());
    }

    public function testPlannedVersionDoesNotEnableModule(): void
    {
        static::assertCount(1, $this->createService(['dep-parent' => []], ['dep-parent' => '2.1.0'])->getUnmetDependencies());
    }

    public function testCoreModuleDependencyIsAlwaysMet(): void
    {
        static::assertSame([], $this->createService(['user' => []])->getUnmetDependencies());
    }

    public function testReportsEachUnmetDependency(): void
    {
        $this->enableModule('dep-parent');

        $messages = $this->createService([
            'dep-parent' => ['minVersion' => '3.0'],
            'dep-other' => [],
        ])->getUnmetDependencies();

        static::assertSame([
            'You must first upgrade the Dependency Parent module to version 3.0 or later.',
            'You must first install and enable the Dependency Other module.',
        ], $messages);
    }

    public function testRegistersOnlyEnabledDeclaredDependencies(): void
    {
        $this->enableModule('dep-parent');
        $this->enableModule('dep-other');
        $this->resetModuleManager();

        $this->createService(['dep-parent' => [], 'dep-child' => []])->registerDependencies();

        static::assertTrue(Yii::$app->moduleManager->hasModule('dep-parent'), 'an enabled dependency must be registered');
        static::assertTrue(Yii::$app->hasModule('dep-parent'), 'an enabled dependency must be available via Yii::$app->getModule()');
        static::assertFalse(Yii::$app->moduleManager->hasModule('dep-child'), 'a disabled dependency must not be registered');
        static::assertFalse(Yii::$app->moduleManager->hasModule('dep-other'), 'a module that is not a dependency must not be registered');
    }

    public function testEnableChecksDependencies(): void
    {
        $module = new Module('dep-child', Yii::$app, ['basePath' => $this->getFixturePath() . '/child']);

        try {
            (new ModuleService($module))->checkRequirements();
            static::fail('Enabling a module with an unmet dependency must fail');
        } catch (ServerErrorHttpException $e) {
            static::assertSame('You must first install and enable the Dependency Parent module.', $e->getMessage());
        }

        $this->enableModule('dep-parent');

        $this->expectException(ServerErrorHttpException::class);
        $this->expectExceptionMessage('You must first upgrade the Dependency Parent module to version 2.1.0 or later.');
        (new ModuleService($module))->checkRequirements();
    }

    public function testSortPutsDependenciesFirst(): void
    {
        $order = ModuleDependencyService::sortByDependencies([
            'child' => $this->moduleInfo(['parent' => []]),
            'unrelated' => $this->moduleInfo([]),
            'grandchild' => $this->moduleInfo(['child' => [], 'not-in-batch' => []]),
            'parent' => $this->moduleInfo([]),
        ]);

        static::assertSame(['parent', 'child', 'unrelated', 'grandchild'], $order);
    }

    public function testSortReturnsAllModulesOfACycle(): void
    {
        $order = ModuleDependencyService::sortByDependencies([
            'a' => $this->moduleInfo(['b' => []]),
            'b' => $this->moduleInfo(['a' => []]),
            'c' => $this->moduleInfo([]),
        ]);

        static::assertEqualsCanonicalizing(['a', 'b'], array_slice($order, 0, 2));
        static::assertSame('c', $order[2]);
    }

    private function createService(array $dependencies, array $plannedVersions = []): ModuleDependencyService
    {
        return new ModuleDependencyService($this->moduleInfo($dependencies), $plannedVersions);
    }

    private function moduleInfo(array $dependencies): array
    {
        return ['id' => 'example', 'humhub' => ['minVersion' => '1.19', 'modules' => $dependencies]];
    }

    private function enableModule(string $moduleId): void
    {
        (new ModuleEnabled(['module_id' => $moduleId]))->save();
    }

    /**
     * Fresh ModuleManager, so its enabled modules (read from the database on init) include
     * the ones enabled by the test and no fixture module is registered.
     */
    private function resetModuleManager(): void
    {
        Yii::$app->cache->flush();
        Yii::$app->set('moduleManager', new ModuleManager());
    }

    private function getFixturePath(): string
    {
        return dirname(__DIR__, 2) . '/_data/ModuleDependencies';
    }

    private function cleanUpFixtureModules(): void
    {
        ModuleEnabled::deleteAll(['module_id' => self::FIXTURE_MODULE_IDS]);

        foreach (self::FIXTURE_MODULE_IDS as $moduleId) {
            Yii::$app->setModule($moduleId, null);
        }

        Yii::$app->cache->flush();
    }
}
