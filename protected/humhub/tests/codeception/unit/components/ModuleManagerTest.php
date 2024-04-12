<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/** @noinspection UnknownInspectionInspection */

namespace humhub\tests\codeception\unit\components;

use humhub\components\bootstrap\ModuleAutoLoader;
use humhub\components\ModuleEvent;
use humhub\components\ModuleManager;
use humhub\exceptions\InvalidArgumentTypeException;
use humhub\models\ModuleEnabled;
use humhub\modules\admin\events\ModulesEvent;
use humhub\tests\codeception\unit\ModuleAutoLoaderTest;
use Some\Name\Space\module1\Module as Module1;
use Some\Name\Space\module2\Module as Module2;
use Some\Name\Space\moduleWithMigration\Module as ModuleWithMigration;
use SplFileInfo;
use tests\codeception\_support\HumHubDbTestCase;
use Throwable;
use Yii;
use yii\base\ErrorException;
use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\caching\ArrayCache;
use yii\db\StaleObjectException;
use yii\helpers\FileHelper;
use yii\log\Logger;
use yii\web\ServerErrorHttpException;

require_once __DIR__ . '/bootstrap/ModuleAutoLoaderTest.php';

/**
 * @since 1.15
 **/
class ModuleManagerTest extends HumHubDbTestCase
{
    private static int $moduleDirCount;
    private static array $moduleDirList;
    private static ?array $moduleEnabledList = null;
    private static string $appModuleRoot;
    private static string $coreModuleRoot;
    private static string $testModuleRoot;
    private ?ModuleManagerMock $moduleManager = null;
    private ?string $moduleId = null;
    private ?array $config = null;
    private ?string $moduleClass = null;
    private ?string $moduleNS = null;
    private static array $aliases;
    private static ModuleManager $originalModuleManager;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::$testModuleRoot = dirname(__DIR__, 2) . '/_data/ModuleConfig';
        static::$coreModuleRoot = dirname(__DIR__, 4) . '/modules';
        static::$appModuleRoot = dirname(__DIR__, 5) . '/modules';

        static::$moduleDirCount = 0;
        static::$moduleDirList = [];

        $dh = opendir($appModuleRoot = static::$appModuleRoot);

        while (false !== ($filename = readdir($dh))) {
            if ($filename !== '.' && $filename !== '..' && is_dir("$appModuleRoot/$filename")) {
                static::$moduleDirCount++;

                $config_file = new SplFileInfo("$appModuleRoot/$filename/config.php");
                $config_file = new SplFileInfo($config_file->getRealPath());

                if (!$config_file->isFile() || !$config_file->isReadable()) {
                    continue;
                }

                $config = include $config_file->getPathname();

                if (!is_array($config)) {
                    continue;
                }

                static::$moduleDirList[$config['id']] = $config['class'];
            }
        }
    }

    protected function tearDown(): void
    {
        $this->reset();

        try {
            Yii::$app->set('moduleManager', static::$originalModuleManager);
        } catch (InvalidConfigException $e) {
        }

        parent::tearDown();
    }

    protected function setUp(): void
    {
        static::$aliases = Yii::$aliases;
        static::$originalModuleManager = Yii::$app->moduleManager;
        /**
         * prevent calling ModuleEnabled::getEnabledIds() from @see ModuleManager::init()
         */
        Yii::$app->params['databaseInstalled'] = false;

        $this->reset();

        $this->config = null;
        $this->moduleId = null;
        $this->moduleClass = null;
        $this->moduleNS = null;

        parent::setUp();
    }

    public function testBasics()
    {
        static::assertEquals(
            [],
            $this->moduleManager->myModules(),
            __FUNCTION__ . '.' . 'myModules()',
        );
        static::assertEquals(
            [],
            $this->moduleManager->myCoreModules(),
            __FUNCTION__ . '.' . 'myCoreModules()',
        );
        static::assertEquals(
            static::$moduleEnabledList,
            $this->moduleManager->myEnabledModules(),
            __FUNCTION__ . '.' . 'myEnabledModules()',
        );
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function testGetModule()
    {
        $this->moduleId = 'non-existing-module';

        static::assertNull($this->moduleManager->getModule($this->moduleId, false));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Could not find/load requested module: ' . $this->moduleId,
        );

        $this->moduleManager->getModule($this->moduleId);
    }

    public function testRegisterNonExistingModulePath()
    {
        $basePath = static::$testModuleRoot . '/nonExistingModule';

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'Module configuration requires an id and class attribute: ' . $basePath,
        );

        $this->moduleManager->register($basePath);
    }

    public function testRegisterInvalidModuleConfig()
    {
        $basePath = static::$testModuleRoot . '/nonExistingModule';

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'Module configuration requires an id and class attribute: ' . $basePath,
        );

        $this->moduleManager->register($basePath, ['foo' => 'bar']);
    }

    public function testRegisterInvalidModuleConfigFromPath()
    {
        $basePath = static::$testModuleRoot . '/invalidModule1';

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'Module configuration requires an id and class attribute: ' . $basePath,
        );

        $this->moduleManager->register($basePath);
    }

    public function testRegisterInvalidModuleConfigFromPathWithProvidedInvalidConfig()
    {
        $basePath = static::$testModuleRoot . '/invalidModule1';

        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage(
            'Module configuration requires an id and class attribute: ' . $basePath,
        );

        $this->moduleManager->register($basePath, ['foo' => 'bar']);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function testRegisterValidModuleConfig()
    {
        [$basePath, $config] = $this->getModuleConfig(static::$testModuleRoot . '/module1');

        $this->registerModule($basePath, $config);

        static::assertEquals(
            [],
            $this->moduleManager->getModules([
                'includeCoreModules' => true,
                'enabled' => true,
                'returnClass' => true,
            ]),
            __FUNCTION__ . '.' . 'getModules()',
        );
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function testRegisterValidModuleConfigFromPath()
    {
        [$basePath] = $this->getModuleConfig(static::$testModuleRoot . '/module1');

        $this->registerModule($basePath, null);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function testRegisterEnabledModuleConfig()
    {
        [$basePath, $config] = $this->getModuleConfig(static::$testModuleRoot . '/module1');

        $this->registerModuleAsEnabled($basePath, $config);

        $this->assertModuleActive();
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function testRegisterIncompatibleEnabledModuleConfig()
    {
        [$basePath, $config] = $this->getModuleConfig(static::$testModuleRoot . '/invalidModule2');

        $this->registerModuleAsEnabled($basePath, $config);

        $this->assertModuleNotActive();
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function testRegisterInstallerModuleWhenInstalled()
    {
        [$basePath, $config] = $this->getModuleConfig(static::$testModuleRoot . '/installerModule');

        static::assertTrue(Yii::$app->isInstalled());

        $this->registerModule($basePath, $config, false);

        $this->assertModuleNotActive();
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function testRegisterInstallerModuleWhenNotInstalled()
    {
        [$basePath, $config] = $this->getModuleConfig(static::$testModuleRoot . '/installerModule');

        Yii::$app->params['installed'] = false;

        $this->registerModule($basePath, $config, true);

        $this->assertModuleActive();

        static::assertFalse($this->moduleManager->isCoreModule($this->moduleId));
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function testRegisterCoreModule()
    {
        [$basePath, $config] = $this->getModuleConfig(static::$testModuleRoot . '/coreModule');

        $this->registerModule($basePath, $config, true);

        $this->assertModuleActive();
    }

    public function testInvalidEventConfigEmpty()
    {
        $this->runEventExceptionTest(null, null);
        $this->runEventExceptionTest('', null);
        $this->runEventExceptionTest([], null);
    }

    public function testInvalidEventConfigAsString()
    {
        $this->runEventExceptionTest('wrong', 'events must be traversable');
    }

    public function testInvalidEventConfigNotArrayAccess()
    {
        $this->runEventExceptionTest([
            'invalid1',
        ], "event configuration must be an array or implement \\ArrayAccess");
    }

    public function testInvalidEventConfigMissingClass()
    {
        $this->runEventExceptionTest([
            [
                'event' => 'invalid1',
            ],
        ], "required property 'class' missing!");
    }

    public function testInvalidEventConfigMissingEvent()
    {
        $this->runEventExceptionTest([
            [
                'class' => __CLASS__,
            ],
        ], "required property 'event' missing!");
    }

    public function testInvalidEventConfigMissingCallback()
    {
        $this->runEventExceptionTest([
            [
                'class' => __CLASS__,
                'event' => 'invalid1',
            ],
        ], "required property 'callback' missing!");
    }

    public function testInvalidEventConfigInvalidCallbackAsClosure()
    {
        $this->runEventExceptionTest([
            [
                'class' => __CLASS__,
                'event' => 'invalid1',
                'callback' => static function () {
                },
            ],
        ], "property 'callback' must be a callable defined in the array-notation denoting a method of a class");
    }

    public function testInvalidEventConfigInvalidCallbackWithEmptyClass()
    {
        $this->runEventExceptionTest([
            [
                'class' => __CLASS__,
                'event' => 'invalid1',
                'callback' => [null, 'test'],
            ],
        ], "class '' does not exist.");
    }

    public function testInvalidEventConfigInvalidCallbackWithNonExistingClass()
    {
        $this->runEventExceptionTest([
            [
                'class' => __CLASS__,
                'event' => 'invalid1',
                'callback' => ['someClass'],
            ],
        ], "class 'someClass' does not exist.");
    }

    public function testInvalidEventConfigInvalidCallbackWithNonExistingMethod()
    {
        $this->runEventExceptionTest(
            [
                [
                    'class' => __CLASS__,
                    'event' => 'invalid1',
                    'callback' => [__CLASS__, 'someMethod'],
                ],
            ],
            "class 'humhub\\tests\\codeception\\unit\\components\\ModuleManagerTest' does not have a method called 'someMethod",
        );
    }

    /**
     * @throws Exception
     */
    public function testGetEnabledModules()
    {
        [$basePath, $config] = $this->getModuleConfig(static::$testModuleRoot . '/module1');

        $this->registerModule($basePath, $config);

        static::assertEquals([], $this->moduleManager->getEnabledModules([
            'returnClass' => true,
        ]));

        $this->reset();

        $this->registerModuleAsEnabled($basePath, $config);

        static::assertEquals([$this->moduleId => $this->moduleClass], $this->moduleManager->getEnabledModules([
            'returnClass' => true,
        ]));

        $moduleManager = Yii::$app->moduleManager;

        static::assertIsArray($modules = $moduleManager->getEnabledModules([
            'includeCoreModules' => true,
            'enabled' => false,
            'returnClass' => true,
        ]));

        // Workaround for internal SaaS core module
        unset($modules['hostinginfo']);

        $locallyEnabledModules = array_intersect_key(static::$moduleDirList, array_flip(array_column(
            static::dbSelect('module_enabled', 'module_id'),
            'module_id',
        )));

        $expected = array_merge(
            [],
            array_flip(ModuleAutoLoaderTest::EXPECTED_CORE_MODULES),
            $locallyEnabledModules,
        );

        static::assertEquals($expected, $modules);

        static::assertIsArray($modules = $moduleManager->getEnabledModules([
            'enabled' => false,
            'returnClass' => true,
        ]));

        static::assertEquals($locallyEnabledModules, $modules);
    }

    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function testCanRemoveModule()
    {
        $this->skipIfMarketplaceNotEnabled();

        [$basePath, $config] = $this->getModuleConfig(static::$testModuleRoot . '/module1');

        // cannot be removed, since it does not exist
        static::assertFalse($this->moduleManager->canRemoveModule($this->moduleId));

        $module = $this->registerModuleAsEnabled($basePath, $config);

        static::assertTrue($this->moduleManager->canRemoveModule($this->moduleId));

        $module->setBasePath(static::$coreModuleRoot . "/admin");

        // cannot be removed, since it does not exist within the marketplace dir
        static::assertFalse($this->moduleManager->canRemoveModule($this->moduleId));
    }

    /**
     * @throws Exception
     * @throws ErrorException
     * @throws InvalidConfigException
     */
    public function testRemoveModule()
    {
        $this->skipIfMarketplaceNotEnabled();

        [$basePath, $config] = $this->getModuleConfig(static::$testModuleRoot . '/module1');

        $module = $this->registerModuleAsEnabled($basePath, $config);

        $tmp = $this->createTempDir();
        $module->setBasePath($tmp);

        static::assertTrue($this->moduleManager->createBackup);
        $backup = $this->moduleManager->removeModule($this->moduleId, false);

        static::assertDirectoryDoesNotExist($tmp);
        static::assertDirectoryExists($backup);

        FileHelper::removeDirectory($backup);

        $this->moduleManager->createBackup = false;

        $tmp = $this->createTempDir();
        $module->setBasePath($tmp);

        $backup = $this->moduleManager->removeModule($this->moduleId, false);

        static::assertNull($backup);
        static::assertDirectoryExists($tmp);

        FileHelper::removeDirectory($tmp);
    }

    /**
     * @noinspection MissedFieldInspection
     */
    /**
     * @throws Throwable
     * @throws InvalidConfigException
     * @throws StaleObjectException
     * @throws Exception
     */
    public function testEnableAndDisableModules()
    {
        $this->moduleManager->on(ModuleManager::EVENT_BEFORE_MODULE_ENABLE, [$this, 'handleEvent']);
        $this->moduleManager->on(ModuleManager::EVENT_AFTER_MODULE_ENABLE, [$this, 'handleEvent']);
        $this->moduleManager->on(ModuleManager::EVENT_BEFORE_MODULE_DISABLE, [$this, 'handleEvent']);
        $this->moduleManager->on(ModuleManager::EVENT_AFTER_MODULE_DISABLE, [$this, 'handleEvent']);

        [$basePath, $config] = $this->getModuleConfig(static::$testModuleRoot . '/module1');

        static::assertRecordCount(0, ModuleEnabled::tableName(), ['module_id' => $this->moduleId]);

        $module = $this->registerModule($basePath, $config);

        static::assertRecordCount(0, ModuleEnabled::tableName(), ['module_id' => $this->moduleId]);

        $this->moduleManager->enable($module);

        static::assertRecordCount(1, ModuleEnabled::tableName(), ['module_id' => $this->moduleId]);

        /** @noinspection MissedFieldInspection */
        $this->assertEvents([
            [
                'class' => ModuleEvent::class,
                'event' => 'beforeModuleEnabled',
                'sender' => $this->moduleManager,
                'data' => null,
                'handled' => false,
                'module' => ['module1' => Module1::class],
            ],
            [
                'class' => ModuleEvent::class,
                'event' => 'afterModuleEnabled',
                'sender' => $this->moduleManager,
                'data' => null,
                'handled' => false,
                'module' => ['module1' => Module1::class],
            ],
        ]);

        $this->moduleManager->disable($module);

        static::assertCount(count(static::$moduleEnabledList), $this->moduleManager->myEnabledModules());
        static::assertRecordCount(0, ModuleEnabled::tableName(), ['module_id' => $this->moduleId]);
        static::assertNull(Yii::$app->getModule($this->moduleId));

        /** @noinspection MissedFieldInspection */
        $this->assertEvents([
            [
                'class' => ModuleEvent::class,
                'event' => 'beforeModuleDisabled',
                'sender' => $this->moduleManager,
                'data' => null,
                'handled' => false,
                'module' => ['module1' => Module1::class],
            ],
            [
                'class' => ModuleEvent::class,
                'event' => 'afterModuleDisabled',
                'sender' => $this->moduleManager,
                'data' => null,
                'handled' => false,
                'module' => ['module1' => Module1::class],
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    public function testGetModules()
    {
        $moduleManager = Yii::$app->moduleManager;

        $modules = $moduleManager->getModules(['returnClass' => true]);

        static::assertIsArray($modules);
        static::assertCount(static::$moduleDirCount, $modules);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function testEnableModules()
    {
        $this->moduleManager->on(ModuleManager::EVENT_AFTER_MODULE_ENABLE, [$this, 'handleEvent']);

        [$basePath, $config] = $this->getModuleConfig(static::$testModuleRoot . '/module1');

        $module = $this->registerModule($basePath, $config);

        $oldMM = Yii::$app->moduleManager;
        Yii::$app->set('moduleManager', $this->moduleManager);

        static::logInitialize();

        $this->moduleManager->enableModules([$module, static::$testModuleRoot . '/module2']);

        Yii::$app->set('moduleManager', $oldMM);

        static::assertNotLog(
            'Module has not been enabled due to beforeEnable() returning false',
            Logger::LEVEL_WARNING,
            [$module->id],
        );
        static::assertLog(
            'Module has no migrations directory.',
            Logger::LEVEL_TRACE,
            [$module->id],
        );

        static::assertNotLog(
            'Module has not been enabled due to beforeEnable() returning false',
            Logger::LEVEL_WARNING,
            ['module2'],
        );
        static::assertLogRegex(
            '@No new migrations found\. Your system is up-to-date\.@',
            Logger::LEVEL_INFO,
            ['module2'],
        );

        static::logReset();

        /** @noinspection MissedFieldInspection */
        $this->assertEvents([
            [
                'class' => ModuleEvent::class,
                'event' => 'afterModuleEnabled',
                'sender' => $this->moduleManager,
                'data' => null,
                'handled' => false,
                'module' => ['module1' => Module1::class],
            ],
            [
                'class' => ModuleEvent::class,
                'event' => 'afterModuleEnabled',
                'sender' => $this->moduleManager,
                'data' => null,
                'handled' => false,
                'module' => ['module2' => Module2::class],
            ],
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Could not find/load requested module: ' . static::$testModuleRoot . '/non-existing-module',
        );

        $this->moduleManager->enableModules([static::$testModuleRoot . '/non-existing-module']);
    }

    /**
     * @noinspection MissedFieldInspection
     */
    public function testEnableModulesWithMigration()
    {
        Yii::$app->set('moduleManager', $this->moduleManager);
        $this->moduleManager->on(ModuleManager::EVENT_AFTER_MODULE_ENABLE, [$this, 'handleEvent']);

        /** @var ModuleWithMigration $module */
        $module = $this->moduleManager->getModule(static::$testModuleRoot . '/moduleWithMigration');

        static::logInitialize();

        // ToDo: beforeEnable() has been removed from this PR and will be re-introduced as an event in a follow-up PR
        //        $module->doEnable = false;
        //        static::assertNull($module->enable());
        //        static::assertNull($module->migrationResult);
        //        static::assertNull($module->migrationOutput);
        //        $this->assertEvents();
        //        static::assertLog('Module has not been enabled due to beforeEnable() returning false', Logger::LEVEL_WARNING, [$module->id]);
        //        static::logFlush();

        $module->doEnable = true;
        static::assertTrue($module->enable());
        //        static::assertEquals(ExitCode::OK, $module->migrationResult);
        //        static::assertNotLog('Module has not been enabled due to beforeEnable() returning false', Logger::LEVEL_WARNING, [$module->id]);
        //        static::assertLogRegex('@\*\*\* applied m230911_000100_create_test_table \(time: \d+\.\d+s\)@', Logger::LEVEL_INFO, [$module->id]);
        static::logFlush();

        $this->assertEvents([
            [
                'class' => ModuleEvent::class,
                'event' => 'afterModuleEnabled',
                'sender' => $this->moduleManager,
                'data' => null,
                'handled' => false,
                'module' => ['moduleWithMigration' => ModuleWithMigration::class],
            ],
        ]);

        //        $module->doDisable = false;
        //        static::assertNull($module->disable());
        //        static::assertNull($module->migrationResult);
        //        static::assertNull($module->migrationOutput);
        //        $this->assertEvents();
        //
        //        static::assertLog('Module has not been disabled due to beforeDisable() returning false', Logger::LEVEL_WARNING, [$module->id]);
        //        static::logFlush();

        $module->doDisable = true;
        static::assertTrue($module->disable());
        //        static::assertEquals(ExitCode::OK, $module->migrationResult);
        //        static::assertNotLog('Module has not been enabled due to beforeEnable() returning false', Logger::LEVEL_WARNING, [$module->id]);
        //        static::assertLogRegex('@    > drop table test_module_with_migration \.\.\. done \(time: \d+\.\d+s\)@', Logger::LEVEL_INFO, [$module->id]);
        static::logFlush();
    }

    /**
     * @noinspection MissedFieldInspection
     */
    public function testEnableModulesWithRequirements()
    {
        Yii::$app->set('moduleManager', $this->moduleManager);

        $moduleWithRequirements = $this->moduleManager->getModule(static::$testModuleRoot . '/moduleWithRequirements');
        $module1 = $this->moduleManager->getModule(static::$testModuleRoot . '/module1');

        $this->expectException(ServerErrorHttpException::class);
        $this->expectExceptionMessage('This module cannot work without enabled module "module1"');
        static::assertFalse($moduleWithRequirements->enable());

        static::assertTrue($module1->enable());
        static::assertTrue($moduleWithRequirements->enable());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testFilterModules()
    {
        Yii::$app->set('moduleManager', $this->moduleManager);

        $this->moduleManager->enableModules([
            static::$testModuleRoot . '/module1',
            static::$testModuleRoot . '/module2',
        ]);

        static::assertEquals(
            ['module1' => Module1::class, 'module2' => Module2::class],
            $this->moduleManager->myModules(),
        );
        static::assertEquals(
            [],
            $this->moduleManager->myCoreModules(),
        );
        static::assertEquals(
            [...static::$moduleEnabledList, 'module1', 'module2'],
            $this->moduleManager->myEnabledModules(),
        );

        $module1 = $this->moduleManager->getModule('module1');
        $module2 = $this->moduleManager->getModule('module2');

        static::assertEquals([], $this->moduleManager->filterModulesByKeyword(null, 'foo'));

        // match keyword
        static::assertEquals(
            ['module1' => $module1],
            $this->moduleManager->filterModulesByKeyword(null, 'one'),
        );
        static::assertEquals(
            ['module2' => $module2],
            $this->moduleManager->filterModulesByKeyword(null, 'two'),
        );
        static::assertEquals(
            ['module1' => $module1, 'module2' => $module2],
            $this->moduleManager->filterModulesByKeyword(null, 'cool'),
        );

        // match name
        static::assertEquals(
            ['module1' => $module1],
            $this->moduleManager->filterModulesByKeyword(null, 'Module 1'),
        );
        static::assertEquals(
            ['module2' => $module2],
            $this->moduleManager->filterModulesByKeyword(null, 'Module 2'),
        );
        static::assertEquals(
            ['module1' => $module1, 'module2' => $module2],
            $this->moduleManager->filterModulesByKeyword(null, 'Example'),
        );

        // match description
        static::assertEquals(
            ['module1' => $module1],
            $this->moduleManager->filterModulesByKeyword(null, 'module 1.'),
        );
        static::assertEquals(
            ['module2' => $module2],
            $this->moduleManager->filterModulesByKeyword(null, 'module 2.'),
        );
        static::assertEquals(
            ['module1' => $module1, 'module2' => $module2],
            $this->moduleManager->filterModulesByKeyword(null, 'testing'),
        );

        $this->moduleManager->on(ModuleManager::EVENT_AFTER_FILTER_MODULES, [$this, 'handleEvent']);

        static::assertEquals(
            ['module1' => $module1, 'module2' => $module2],
            $this->moduleManager->filterModules(null, ['foo']),
        );

        /** @noinspection MissedFieldInspection */
        $this->assertEvents([
            [
                'class' => ModulesEvent::class,
                'event' => 'afterFilterModules',
                'sender' => $this->moduleManager,
                'data' => null,
                'handled' => false,
                'modules' => ['module1' => 'My Example Module 1', 'module2' => 'My Example Module 2'],
            ],
        ]);

        static::assertEquals([], $this->moduleManager->filterModules(null, ['keyword' => 'foo']));

        /** @noinspection MissedFieldInspection */
        $this->assertEvents([
            [
                'class' => ModulesEvent::class,
                'event' => 'afterFilterModules',
                'sender' => $this->moduleManager,
                'data' => null,
                'handled' => false,
                'modules' => [],
            ],
        ]);

        static::assertEquals(
            ['module2' => $module2],
            $this->moduleManager->filterModules(null, ['keyword' => 'Example Module 2']),
        );

        /** @noinspection MissedFieldInspection */
        $this->assertEvents([
            [
                'class' => ModulesEvent::class,
                'event' => 'afterFilterModules',
                'sender' => $this->moduleManager,
                'data' => null,
                'handled' => false,
                'modules' => ['module2' => 'My Example Module 2'],
            ],
        ]);

        $this->expectException(InvalidArgumentTypeException::class);
        $this->expectExceptionMessage(
            'Argument $filters passed to humhub\components\ModuleManager::filterModules must be one of the following types: array, ArrayAccess - string given.',
        );
        static::assertEquals([], $this->moduleManager->filterModules(null, 'foo'));
    }

    /**
     * @throws InvalidConfigException
     */
    public function testFlushCache()
    {
        $oldCache = Yii::$app->cache;

        Yii::$app->set('cache', new ArrayCache());
        static::assertInstanceOf(ArrayCache::class, $cache = Yii::$app->cache);

        static::assertFalse($cache->get(ModuleAutoLoader::CACHE_ID));
        Yii::$app->cache->set(ModuleAutoLoader::CACHE_ID, ['foo' => 'bar']);
        static::assertEquals(['foo' => 'bar'], $cache->get(ModuleAutoLoader::CACHE_ID));

        $this->moduleManager->flushCache();

        static::assertFalse($cache->get(ModuleAutoLoader::CACHE_ID));

        Yii::$app->set('cache', $oldCache);
    }

    /**
     * @param string $basePath
     *
     * @return array
     */
    public function getModuleConfig(string $basePath): array
    {
        $this->config = require "$basePath/config.php";
        $this->moduleId = $this->config['id'];
        $this->moduleNS = str_replace('\\', '/', $this->config['namespace'] ?? '');
        $this->moduleClass = $this->config['class'];

        return [$basePath, $this->config];
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function registerModule(string $basePath, $config, ?bool $isEnabled = null)
    {
        $isEnabled ??= in_array($this->moduleId, $this->moduleManager->myEnabledModules(), true);
        $isCore = $this->config['isCoreModule'] ?? false;

        if ($isEnabled) {
            // check url-manager
            if (isset($this->config['urlManagerRules'])) {
                $urlManager = Yii::$app->urlManager;
                $rules = $urlManager->rules;
            }

            // check module on app
            if (!is_array($moduleConfig = Yii::$app->modules[$this->moduleId] ?? null)) {
                $moduleConfig = [
                    'class' => $this->moduleClass,
                    'modules' => $this->config['modules'] ?? [],
                ];
            }
        }

        if ($this->moduleNS) {
            static::assertFalse(Yii::getAlias("@$this->moduleNS", false));
        }

        static::assertFalse(Yii::getAlias("@$this->moduleId", false));

        $this->moduleManager->register($basePath, $config);

        static::assertEquals(
            [$this->moduleId => $this->moduleClass],
            $this->moduleManager->myModules(),
            __FUNCTION__ . '.' . 'myModules()',
        );

        static::assertEquals(
            $isCore ? [$this->moduleClass] : [],
            $this->moduleManager->myCoreModules(),
            __FUNCTION__ . '.' . 'myCoreModules()',
        );

        $expected = static::$moduleEnabledList;
        if ($isEnabled && !$isCore) {
            $expected[] = $this->moduleId;
        }
        static::assertEquals(
            $expected,
            $this->moduleManager->myEnabledModules(),
            __FUNCTION__ . '.' . 'myEnabledModules()',
        );

        static::assertTrue($this->moduleManager->hasModule($this->moduleId));


        if ($isEnabled) {
            // check module on app
            static::assertEquals($moduleConfig, Yii::$app->getModules()[$this->moduleId]);
            static::assertInstanceOf($this->moduleClass, Yii::$app->getModule($this->moduleId));
        } else {
            static::assertNull(Yii::$app->getModule($this->moduleId, false));
            static::assertNull(Yii::$app->getModule($this->moduleId, true));
        }

        static::assertInstanceOf($this->moduleClass, $module = $this->moduleManager->getModule($this->moduleId));
        //        $module->setBasePath($basePath);

        static::assertEquals($isCore, $this->moduleManager->isCoreModule($this->moduleId));

        // check if alias has been set
        if ($this->moduleNS) {
            static::assertEquals("$basePath", Yii::getAlias("@$this->moduleNS"));
        }

        // check if alias has been set
        static::assertEquals("$basePath", Yii::getAlias("@$this->moduleId"));

        static::assertEquals([$this->moduleId => $this->moduleClass], $this->moduleManager->getModules([
            'includeCoreModules' => true,
            'enabled' => false,
            'returnClass' => true,
        ]));

        if ($config === null) {
            return $module;
        }

        // check url-manager
        if ($isEnabled && isset($this->config['urlManagerRules'])) {
            static::assertEquals($rules, $urlManager->rules);
        }

        foreach ($this->config['events'] ?? [] as $event) {
            $eventClass = $event['class'] ?? $event[0] ?? null;
            $eventName = $event['event'] ?? $event[1] ?? null;
            $eventHandler = $event['callback'] ?? $event[2] ?? null;

            if (
                $isEnabled && $eventClass && $eventName && is_array($eventHandler) && method_exists(
                    $eventHandler[0],
                    $eventHandler[1],
                )
            ) {
                static::assertTrue(Event::off($eventClass, $eventName, $eventHandler));
            } else {
                static::assertFalse(Event::off($eventClass, $eventName, $eventHandler));
            }
        }

        return $module;
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function registerModuleAsEnabled(string $basePath, $config)
    {
        // set module as enabled
        $this->moduleManager->myEnabledModules()[] = $this->moduleId;

        $module = $this->registerModule($basePath, $config);

        static::assertEquals([$this->moduleId => $this->moduleClass], $this->moduleManager->getModules([
            'includeCoreModules' => true,
            'enabled' => true,
            'returnClass' => true,
        ]));

        return $module;
    }

    public function handleEvent(Event $event, array $eventData = [])
    {
        $e = [];

        if ($event instanceof ModuleEvent) {
            $e['module'] = [$event->moduleId => get_class($event->module)];
        }

        if ($event instanceof ModulesEvent) {
            $e['modules'] = array_column($event->modules, 'name', 'id');
        }

        parent::handleEvent($event, $e);
    }

    public function runEventExceptionTest($events, ?string $exceptionMessage): void
    {
        $this->moduleManager = new ModuleManagerMock();

        [$basePath, $config] = $this->getModuleConfig(static::$testModuleRoot . '/module1');

        // set module as enabled
        $this->moduleManager->myEnabledModules()[] = $this->moduleId;

        unset($config['namespace']);

        $config['strict'] = true;
        $config['events'] = $events;

        if ($exceptionMessage !== null) {
            $this->expectException(InvalidConfigException::class);
            $this->expectExceptionMessage($exceptionMessage);
        }

        $this->moduleManager->register($basePath, $config);
    }

    /**
     * @throws Exception
     */
    public function assertModuleActive(): void
    {
        static::assertInstanceOf($this->moduleClass, $this->moduleManager->getModules([
            'includeCoreModules' => true,
            'enabled' => true,
            'returnClass' => false,
        ])[$this->moduleId]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function assertModuleNotActive(): void
    {
        static::assertEquals([], $this->moduleManager->getModules([
            'includeCoreModules' => true,
            'enabled' => true,
            'returnClass' => false,
        ]));
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        if ($this->moduleManager) {
            foreach ($this->moduleManager->myEnabledModules() as $enableModule) {
                Yii::$app->setModule($enableModule, null);
            }
        }

        Yii::$aliases = static::$aliases;

        $this->firedEvents = [];

        static::dbDelete(ModuleEnabled::tableName(), [
            'module_id' => [
                'module1',
                'module2',
                'moduleWithMigration',
                'moduleWithRequirements',
                'coreModule',
                'installerModule',
                'invalidModule1',
                'invalidModule2',
            ],
        ]);

        if (Yii::$app->isDatabaseInstalled()) {
            static::$moduleEnabledList ??= array_column(
                static::dbSelect('module_enabled', 'module_id'),
                'module_id',
            );
        } else {
            static::$moduleEnabledList ??= [];
        }

        $this->moduleManager = new ModuleManagerMock();
    }

    public function skipIfMarketplaceNotEnabled(): void
    {
        $marketplaceModule = Yii::$app->getModule('marketplace');

        if ($marketplaceModule === null) {
            $this->markTestSkipped();
        }

        Yii::setAlias($marketplaceModule->modulesPath, static::$testModuleRoot);
    }

    /**
     * @return bool|string
     * @throws Exception
     */
    public function createTempDir()
    {
        $tmp = tempnam(sys_get_temp_dir(), "humhub_" . time() . "_");
        unlink($tmp);
        FileHelper::createDirectory($tmp);

        static::assertDirectoryExists($tmp);

        return $tmp;
    }
}
