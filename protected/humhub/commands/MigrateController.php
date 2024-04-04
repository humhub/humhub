<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use humhub\components\Module;
use humhub\helpers\DatabaseHelper;
use humhub\services\MigrationService;
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
class MigrateController extends \yii\console\controllers\MigrateController
{
    /**
     * @var string the directory storing the migration classes. This can be either
     * a path alias or a directory.
     */
    public $migrationPath = '@humhub/migrations';

    /**
     * @var bool also include migration paths of all enabled modules
     */
    public bool $includeModuleMigrations = false;

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
        return parent::beforeAction($action);
    }


    /**
     * @inheritdoc
     */
    public function options($actionID): array
    {
        if ($actionID === 'up') {
            return array_merge(parent::options($actionID), ['includeModuleMigrations']);
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
        if (!$this->includeModuleMigrations) {
            return parent::getNewMigrations();
        }

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
        if ($this->includeModuleMigrations) {
            $this->migrationPath = $this->getMigrationPath($class);
        }

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
     * Returns the migration paths of all enabled modules
     *
     * @return string[]
     */
    protected function getMigrationPaths(): array
    {
        $migrationPaths = ['base' => $this->migrationPath];
        foreach (($this->module ?? Yii::$app)->getModules() as $id => $config) {
            $class = null;
            if (is_array($config) && isset($config['class'])) {
                $class = $config['class'];
            } elseif ($config instanceof Module) {
                $class = get_class($config);
            }

            if ($class !== null) {
                $reflector = new \ReflectionClass($class);
                $path = dirname($reflector->getFileName()) . '/migrations';
                if (is_dir($path)) {
                    $migrationPaths[$id] = $path;
                }
            }
        }

        return $migrationPaths;
    }

    /**
     * Executes all pending migrations
     *
     * @param string $action 'up' or 'new'
     * @param \yii\base\Module|null $module Module to get the migrations from, or Null for Application
     *
     * @return string output
     * @deprecated since 1.16; use MigrationService::migrateUp()
     * @see MigrationService::migrateUp()
     */
    public static function webMigrateAll(string $action = 'up', ?\yii\base\Module $module = null): string
    {
        return $action === 'up'
            ? MigrationService::create($module)->migrateUp()
            : MigrationService::create($module)->migrateNew();
    }

    /**
     * Executes migrations in a specific folder
     *
     * @param string $migrationPath
     *
     * @return string output
     * @deprecated since 1.16; use MigrationService::create($module)->migrateUp()
     * @see MigrationService::create()
     * @see MigrationService::migrateUp()
     */
    public static function webMigrateUp(string $migrationPath): ?string
    {
        ob_start();
        $controller = new self('migrate', Yii::$app);
        $controller->db = Yii::$app->db;
        $controller->interactive = false;
        $controller->migrationPath = $migrationPath;
        $controller->color = false;
        $controller->runAction('up');

        return ob_get_clean() ?: null;
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
