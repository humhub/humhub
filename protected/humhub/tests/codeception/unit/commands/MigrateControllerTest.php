<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\commands;

use humhub\commands\MigrateController;
use humhub\models\ModuleEnabled;
use ReflectionMethod;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Verifies that the application-wide migration scan only applies migrations of enabled
 * (and core) modules. A module that is merely present in the modules directory but never
 * enabled must not have its migrations applied.
 *
 * @since 1.19
 */
class MigrateControllerTest extends HumHubDbTestCase
{
    /**
     * Test fixture module that ships a real migrations/ directory but is not a core module.
     * Its config.php id (used by locateModuleConfigs / ModuleEnabled) is "moduleWithMigration".
     */
    private const FIXTURE_MODULE_ID = 'moduleWithMigration';

    private array $originalAutoloadPaths;

    protected function setUp(): void
    {
        parent::setUp();

        // Make the _data/ModuleConfig fixture modules discoverable in addition to the
        // regular module paths so that "moduleWithMigration" shows up on the filesystem.
        $this->originalAutoloadPaths = Yii::$app->params['moduleAutoloadPaths'];
        Yii::$app->params['moduleAutoloadPaths'][] = dirname(__DIR__, 2) . '/_data/ModuleConfig';

        $this->forgetFixtureModule();
    }

    protected function tearDown(): void
    {
        $this->forgetFixtureModule();
        Yii::$app->params['moduleAutoloadPaths'] = $this->originalAutoloadPaths;

        parent::tearDown();
    }

    public function testDisabledModuleMigrationsAreExcluded(): void
    {
        $paths = $this->resolveMigrationPaths();

        static::assertArrayHasKey('base', $paths, 'core base migrations must always be included');
        static::assertArrayNotHasKey(
            self::FIXTURE_MODULE_ID,
            $paths,
            'a module present on disk but not enabled must not contribute migrations',
        );
    }

    public function testEnabledModuleMigrationsAreIncluded(): void
    {
        (new ModuleEnabled(['module_id' => self::FIXTURE_MODULE_ID]))->save();

        $paths = $this->resolveMigrationPaths();

        static::assertArrayHasKey(
            self::FIXTURE_MODULE_ID,
            $paths,
            'an enabled module must contribute its migrations',
        );
    }

    private function resolveMigrationPaths(): array
    {
        // Drop cached module configs and enabled-module ids so each scan reflects current state.
        Yii::$app->cache->flush();

        $controller = new MigrateController('migrate', Yii::$app, ['includeModuleMigrations' => 1]);

        $method = new ReflectionMethod($controller, 'getMigrationPaths');
        $method->setAccessible(true);

        return $method->invoke($controller);
    }

    private function forgetFixtureModule(): void
    {
        ModuleEnabled::deleteAll(['module_id' => self::FIXTURE_MODULE_ID]);
        Yii::$app->cache->flush();
    }
}
