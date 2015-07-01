<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * MigrateController
 * 
 * @author Luke
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
        foreach (\Yii::$app->getModules() as $id => $config) {
            if (is_array($config)) {
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
     * Allow to execute all migrations via web
     */
    public static function webMigrateAll()
    {
        defined('STDOUT') or define('STDOUT', fopen('php://output', 'w'));
        defined('STDERR') or define('STDERR', fopen('php://output', 'w'));

        ob_start();
        $controller = new self('migrate', Yii::$app);
        $controller->db = Yii::$app->db;
        $controller->interactive = false;
        $controller->includeModuleMigrations = true;
        $controller->color = false;
        $controller->runAction('up');
        return ob_get_clean();
    }

}
