<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\commands;

use humhub\commands\MigrateController;
use humhub\components\ModuleManager;
use humhub\models\ModuleEnabled;
use ReflectionMethod;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * Covers the application-wide migration scan of MigrateController:
 *
 * - Only enabled (and core) modules contribute migrations; a module that is merely
 *   present in the modules directory must not have its migrations applied.
 * - Enabled modules are fully registered before their migrations run, so migrations
 *   get the same context as in the web application: namespace aliases from config.php
 *   (the module id may differ from the namespace, e.g. "auth-keycloak" vs.
 *   humhub\modules\authKeycloak) and a module instance via Yii::$app->getModule().
 * - A module whose registration fails (e.g. a stale module during a core upgrade)
 *   is skipped with a warning instead of failing the whole migration run.
 *
 * @since 1.19
 */
class MigrateControllerTest extends HumHubDbTestCase
{
    /**
     * Fixture module whose id deliberately differs from its PHP namespace
     * (Some\Name\Space\mismatch), mirroring modules like auth-keycloak.
     */
    private const MISMATCH_MODULE_ID = 'auth-mismatch';

    /**
     * Fixture module whose registration always throws (strict + invalid events).
     */
    private const BROKEN_MODULE_ID = 'broken-registration';

    private array $originalAutoloadPaths;
    private array $originalAliases;
    private ModuleManager $originalModuleManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalAliases = Yii::$aliases;
        $this->originalModuleManager = Yii::$app->moduleManager;

        // Make the _data/ModuleConfig fixture modules discoverable in addition to the
        // regular module paths.
        $this->originalAutoloadPaths = Yii::$app->params['moduleAutoloadPaths'];
        Yii::$app->params['moduleAutoloadPaths'][] = dirname(__DIR__, 2) . '/_data/ModuleConfig';

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

    public function testDisabledModuleMigrationsAreExcluded(): void
    {
        $paths = $this->resolveMigrationPaths();

        static::assertArrayHasKey('base', $paths, 'core base migrations must always be included');
        static::assertArrayNotHasKey(
            self::MISMATCH_MODULE_ID,
            $paths,
            'a module present on disk but not enabled must not contribute migrations',
        );
        static::assertNull(
            Yii::$app->getModule(self::MISMATCH_MODULE_ID, false),
            'a disabled module must not be registered as application module',
        );
    }

    public function testEnabledModuleMigrationsAreIncluded(): void
    {
        $this->enableModule(self::MISMATCH_MODULE_ID);

        $paths = $this->resolveMigrationPaths();

        static::assertArrayHasKey(
            self::MISMATCH_MODULE_ID,
            $paths,
            'an enabled module must contribute its migrations',
        );
    }

    public function testEnabledModuleIsFullyRegisteredForMigrations(): void
    {
        $this->enableModule(self::MISMATCH_MODULE_ID);

        $this->resolveMigrationPaths();

        // The namespace alias must come from config.php's 'namespace' — an alias derived
        // from the module id ("auth-mismatch") could never resolve Some\Name\Space\mismatch.
        static::assertEquals(
            dirname(__DIR__, 2) . '/_data/ModuleConfig/moduleIdNamespaceMismatch',
            Yii::getAlias('@Some/Name/Space/mismatch', false),
            'the module namespace alias from config.php must be registered so migration code can autoload module classes',
        );

        // Instantiation loads the Module class through the namespace alias and gives
        // migrations access to $module->settings etc. — same context as the web application.
        static::assertInstanceOf(
            'Some\Name\Space\mismatch\Module',
            Yii::$app->getModule(self::MISMATCH_MODULE_ID),
            'an enabled module must be available via Yii::$app->getModule() while its migrations run',
        );
    }

    public function testBrokenModuleRegistrationIsContained(): void
    {
        $this->enableModule(self::BROKEN_MODULE_ID);
        $this->enableModule(self::MISMATCH_MODULE_ID);

        $paths = $this->resolveMigrationPaths();

        static::assertArrayNotHasKey(
            self::BROKEN_MODULE_ID,
            $paths,
            'a module that fails to register must have its migrations skipped',
        );
        static::assertArrayHasKey(
            self::MISMATCH_MODULE_ID,
            $paths,
            'other modules must still be migrated when one module fails to register',
        );
        static::assertArrayHasKey('base', $paths);
    }

    private function enableModule(string $moduleId): void
    {
        (new ModuleEnabled(['module_id' => $moduleId]))->save();
    }

    private function resolveMigrationPaths(): array
    {
        // Drop cached module configs and enabled-module ids so the scan reflects current state.
        Yii::$app->cache->flush();

        // Fresh ModuleManager so its enabled-modules list (read from DB on init) includes
        // the ModuleEnabled records inserted by the test.
        Yii::$app->set('moduleManager', new ModuleManager());

        $controller = new MigrateController('migrate', Yii::$app, ['includeModuleMigrations' => 1]);

        $method = new ReflectionMethod($controller, 'getMigrationPaths');
        $method->setAccessible(true);

        return $method->invoke($controller);
    }

    private function cleanUpFixtureModules(): void
    {
        ModuleEnabled::deleteAll(['module_id' => [self::MISMATCH_MODULE_ID, self::BROKEN_MODULE_ID]]);

        Yii::$app->setModule(self::MISMATCH_MODULE_ID, null);
        Yii::$app->setModule(self::BROKEN_MODULE_ID, null);

        Yii::$app->cache->flush();
    }
}
