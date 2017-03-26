<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use Yii;

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
 */
class MigrateController extends \yii\console\controllers\MigrateController
{

    /**
     * @var string the directory storing the migration classes. This can be either
     * a path alias or a directory.
     */
    public $migrationPath = '@humhub/migrations';

    /**
     * @var boolean also include migration paths of all enabled modules
     */
    public $includeModuleMigrations = false;

    /**
     * When includeModuleMigrations is enabled, this maps migrations to the
     * corresponding module.
     *
     * @var array
     */
    protected $migrationPathMap = [];

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        if ($actionID == 'up') {
            return array_merge(parent::options($actionID), ['includeModuleMigrations']);
        }

        return parent::options($actionID);
    }

    /**
     * Returns the migrations that are not applied.
     * @return array list of new migrations
     */
    protected function getNewMigrations()
    {
        if (!$this->includeModuleMigrations) {
            return parent::getNewMigrations();
        }

        $this->migrationPathMap = [];
        $migrations = [];
        foreach ($this->getMigrationPaths() as $migrationPath) {
            $this->migrationPath = $migrationPath;
            $migrations = array_merge($migrations, parent::getNewMigrations());
            $this->migrationPathMap[$migrationPath] = $migrations;
        }

        sort($migrations);

		return $migrations;
    }

    /**
     * Creates a new migration instance.
     * @param string $class the migration class name
     * @return \yii\db\MigrationInterface the migration instance
     */
    protected function createMigration($class)
    {
        if ($this->includeModuleMigrations) {
            $this->migrationPath = $this->getMigrationPath($class);
        }

		return parent::createMigration($class);
    }

    /**
     * Returns the migration path of a given migration.
     * A map containing the path=>migration will be created by getNewMigrations method.
     *
     * @param type $migration
     * @return type
     * @throws \yii\console\Exception
     */
    public function getMigrationPath($migration)
    {
        foreach ($this->migrationPathMap as $path => $migrations) {
            if (in_array($migration, $migrations)) {
                return $path;
            }
        }

		throw new \yii\console\Exception("Could not find path for: " . $migration);
    }

    /**
     * Returns the migration paths of all enabled modules
     *
     * @return array
     */
    protected function getMigrationPaths()
    {
        $migrationPaths = ['base' => $this->migrationPath];
        foreach (Yii::$app->getModules() as $id => $config) {
            if (is_array($config) && isset($config['class'])) {
                $reflector = new \ReflectionClass($config['class']);
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
     * @return string output
     */
    public static function webMigrateAll()
    {
        ob_start();
        $controller = new self('migrate', Yii::$app);
        $controller->db = Yii::$app->db;
        $controller->interactive = false;
        $controller->includeModuleMigrations = true;
        $controller->color = false;
        $controller->runAction('up');

		return ob_get_clean();
    }

    /**
     * Executes migrations in a specific folder
     *
     * @param string $migrationPath
     * @return string output
     */
    public static function webMigrateUp($migrationPath)
    {
        ob_start();
        $controller = new self('migrate', Yii::$app);
        $controller->db = Yii::$app->db;
        $controller->interactive = false;
        $controller->migrationPath = $migrationPath;
        $controller->color = false;
        $controller->runAction('up');
        return ob_get_clean();
    }

    /**
     * @inheritdoc
     */
    public function stdout($string)
    {
        if (Yii::$app instanceof \yii\web\Application) {
            print $string;
        } else {
            return parent::stdout($string);
        }
    }

    /**
     * @inheritdoc
     */
    public function stderr($string)
    {
        if (Yii::$app instanceof \yii\web\Application) {
            print $string;
        } else {
            return parent::stderr($string);
        }
    }

}
