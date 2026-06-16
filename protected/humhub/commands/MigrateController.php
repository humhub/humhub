<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use humhub\components\console\WithoutModuleAutoload;
use humhub\components\Module;
use humhub\helpers\DatabaseHelper;
use humhub\services\ModuleDiscoveryService;
use Yii;
use yii\console\controllers\BaseMigrateController;
use yii\console\Exception;
use yii\db\Migration;
use yii\db\MigrationInterface;
use yii\web\Application;

/**
 * Manages application migrations.
 *
 * A migration means a set of persistent changes to the application environment
 * that is shared among different developers. For example, in an application
 * backed by a database, a migration may refer to a set of changes to
 * the database, such as creating a new table, adding a new table column.
 *
 * This command provides support for tracking the migration history, upgrading
 * or downloading with migrations, and creating new migration skeletons.
 *
 * The migration history is stored in a database table named
 * as [[migrationTable]]. The table will be automatically created the first time
 * this command is executed, if it does not exist. You may also manually
 * create it as follows:
 *
 * ~~~
 * CREATE TABLE migration (
 *     version varchar(180) PRIMARY KEY,
 *     apply_time integer
 * )
 * ~~~
 *
 * Below are some common usages of this command:
 *
 * ~~~
 * # creates a new migration named 'create_user_table'
 * yii migrate/create create_user_table
 *
 * # applies ALL new migrations
 * yii migrate
 *
 * # reverts the last applied migration
 * yii migrate/down
 * ~~~
 *
 *
 * @property-read string[] $newMigrations
 * @property-read string[] $migrationPaths
 */
#[WithoutModuleAutoload]
class MigrateController extends \yii\console\controllers\MigrateController
{
    /**
     * @var string the directory storing the migration classes. This can be either
     * a path alias or a directory.
     */
    public $migrationPath = '@humhub/migrations';

    /**
     * Whether to include migration paths of all enabled modules (1=yes, 0=no).
     *
     * Use 0/1 on the CLI — Yii2 coerces CLI values via settype(), so the string
     * "false" becomes bool true. Both --includeModuleMigrations=0 and
     * --includeModuleMigrations=false map to falsy with an int property.
     *
     * @var int
     */
    public int $includeModuleMigrations = 1;

    /**
     * When set, only migrations for the specified module are applied.
     * Mutually exclusive with --includeModuleMigrations=false.
     *
     * Example: yii migrate/up --moduleId=calendar
     *
     * @var string|null
     */
    public ?string $moduleId = null;

    /**
     * When includeModuleMigrations is enabled, this maps migrations to the
     * corresponding module.
     *
     * @var array
     */
    protected array $migrationPathMap = [];

    /**
     * Stores the last executed migration.
     *
     * @var Migration|null
     */
    private ?Migration $lastMigration = null;


    /**
     * @inerhitdoc
     */
    public function beforeAction($action): bool
    {
        // Make sure to define a default table storage engine
        $db = Yii::$app->db;

        try {
            $db->open();
        } catch (\Throwable $ex) {
            DatabaseHelper::handleConnectionErrors($ex);
        }

        if (in_array($db->getDriverName(), ['mysql', 'mysqli'], true)) {
            $db->pdo->exec('SET default_storage_engine=' . Yii::$app->params['databaseDefaultStorageEngine']);
        }

        Yii::$app->cache->flush();
        return parent::beforeAction($action);
    }


    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        if ($actionID === 'up') {
            return array_merge(parent::options($actionID), ['includeModuleMigrations', 'moduleId']);
        }

        return parent::options($actionID);
    }

    /**
     * Returns the migrations that are not applied.
     *
     * @return string[] list of new migrations
     */
    protected function getNewMigrations(): array
    {
        $this->migrationPathMap = [];
        $migrations = [];
        foreach ($this->getMigrationPaths() as $migrationPath) {
            $this->migrationPath = $migrationPath;
            $newMigrations = parent::getNewMigrations();
            $migrations = array_unique(array_merge($migrations, $newMigrations));
            $this->migrationPathMap[$migrationPath] = $newMigrations;
        }

        sort($migrations);

        return $migrations;
    }

    /**
     * Creates a new migration instance.
     *
     * @param string $class the migration class name
     *
     * @return MigrationInterface the migration instance
     */
    protected function createMigration($class): MigrationInterface
    {
        $this->migrationPath = $this->getMigrationPath($class);

        /**
         * Storing the last executed migration
         *
         * @see BaseMigrateController::migrateUp()
         * @see BaseMigrateController::migrateDown()
         * */
        return $this->lastMigration = parent::createMigration($class);
    }

    /**
     * Returns the migration path of a given migration.
     * A map containing the path=>migration will be created by getNewMigrations method.
     *
     * @param string $migration
     *
     * @return string
     * @throws Exception
     */
    public function getMigrationPath(string $migration): string
    {
        foreach ($this->migrationPathMap as $path => $migrations) {
            if (in_array($migration, $migrations, true)) {
                return $path;
            }
        }

        throw new Exception("Could not find path for: " . $migration);
    }

    /**
     * Returns the migration paths of all enabled modules.
     *
     * @return string[]
     */
    protected function getMigrationPaths(): array
    {
        // Single-module mode: run migrations only for one specific module.
        // Intended to be called after core migrations have already run (e.g. from module/update-all).
        if ($this->moduleId !== null) {
            // Module is normally registered at bootstrap. Fall back to manual registration if
            // it was skipped (e.g. its config.php threw during the initial bootstrap scan).
            $module = Yii::$app->moduleManager->getModule($this->moduleId, false);
            if ($module !== null) {
                $migrationsPath = $module->getBasePath() . DIRECTORY_SEPARATOR . 'migrations';
                return is_dir($migrationsPath) ? [$this->moduleId => $migrationsPath] : [];
            }

            $basePath = ModuleDiscoveryService::findModuleBasePath($this->moduleId);
            if ($basePath === null) {
                return [];
            }

            try {
                Yii::$app->moduleManager->register($basePath);
            } catch (\Throwable $e) {
                Yii::error('Cannot load module config for "' . $this->moduleId . '": ' . $e->getMessage());
                return [];
            }

            $migrationsPath = $basePath . DIRECTORY_SEPARATOR . 'migrations';
            return is_dir($migrationsPath) ? [$this->moduleId => $migrationsPath] : [];
        }

        // Base path + core module migrations are always included.
        // Third-party modules are only added when includeModuleMigrations is set — with
        // #[WithoutModuleAutoload] they are not registered at bootstrap to avoid stale
        // configs executing against a newer core during upgrades.
        // locateModuleConfigs() is used without registerBulk() to avoid contaminating a
        // mock ModuleManager injected by tests via Yii::$app->set('moduleManager').
        $migrationPaths = ['base' => $this->migrationPath];

        if ($this->includeModuleMigrations) {
            // Register namespace aliases for all installed modules so their classes are
            // autoloadable during migration execution. Reads only module.json — no config.php
            // is executed, so broken configs cannot interfere.
            foreach (ModuleDiscoveryService::findInstalledModules() as $moduleId => $info) {
                Yii::setAlias('@humhub/modules/' . $moduleId, $info['basePath']);
                if (Yii::getAlias('@' . $moduleId, false) === false) {
                    Yii::setAlias('@' . $moduleId, $info['basePath']);
                }
            }

            foreach (ModuleDiscoveryService::locateModuleConfigs() as $basePath => $config) {
                $migrationsPath = $basePath . DIRECTORY_SEPARATOR . 'migrations';
                if (is_dir($migrationsPath)) {
                    $migrationPaths[$config['id']] = $migrationsPath;
                }
            }
        }

        // Core modules are always registered at bootstrap even with #[WithoutModuleAutoload].
        // Use reflection to find their migration directories.
        foreach (Yii::$app->getModules() as $id => $config) {
            if (isset($migrationPaths[$id])) {
                continue;
            }

            $class = null;
            if (is_array($config) && isset($config['class'])) {
                $class = $config['class'];
            } elseif ($config instanceof Module) {
                $class = $config::class;
            }

            if ($class !== null) {
                try {
                    $reflector = new \ReflectionClass($class);
                    $path = dirname($reflector->getFileName()) . '/migrations';
                    if (is_dir($path)) {
                        $migrationPaths[$id] = $path;
                    }
                } catch (\Throwable) {
                    // module class not loadable — skip
                }
            }
        }

        return $migrationPaths;
    }

    /**
     * @inheritdoc
     */
    public function stdout($string)
    {
        if (Yii::$app instanceof Application) {
            print $string;
            return strlen($string);
        }

        return parent::stdout($string);
    }

    /**
     * @inheritdoc
     */
    public function stderr($string)
    {
        if (Yii::$app instanceof Application) {
            print $string;
            return strlen($string);
        }

        return parent::stderr($string);
    }

    /**
     * @return mixed
     */
    public function getLastMigration()
    {
        return $this->lastMigration;
    }
}
