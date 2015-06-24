<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace humhub\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * This command executes migrations of all active modules.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MigrateController extends \yii\console\controllers\MigrateController
{

    /**
     * @var string the default command action.
     */
    public $defaultAction = 'up-all';

    /**
     * @var string the directory storing the migration classes. This can be either
     * a path alias or a directory.
     */
    public $migrationPath = '@humhub/migrations';
    
    /**
     * Upgrades all the application + enabled modules by applying new migrations.
     * For example,
     *
     * ~~~
     * yii migrate up-all    # apply all new migrations
     * ~~~
     *
     * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionUpAll($limit = 0)
    {
        foreach ($this->getMigrationPaths() as $migrationPath) {

            $this->migrationPath = $migrationPath;

            $migrations = $this->getNewMigrations();
            if (empty($migrations)) {
                $this->stdout("No new migration found at " . $this->migrationPath . ".\n", Console::FG_GREEN);
                continue;
            }

            $total = count($migrations);
            $limit = (int) $limit;
            if ($limit > 0) {
                $migrations = array_slice($migrations, 0, $limit);
            }

            $n = count($migrations);
            if ($n === $total) {
                $this->stdout("Total $n new " . ($n === 1 ? 'migration' : 'migrations') . " to be applied:\n", Console::FG_YELLOW);
            } else {
                $this->stdout("Total $n out of $total new " . ($total === 1 ? 'migration' : 'migrations') . " to be applied:\n", Console::FG_YELLOW);
            }

            foreach ($migrations as $migration) {
                $this->stdout("\t$migration\n");
            }
            $this->stdout("\n");

            if ($this->confirm('Apply the above ' . ($n === 1 ? 'migration' : 'migrations') . "?")) {
                foreach ($migrations as $migration) {
                    if (!$this->migrateUp($migration)) {
                        $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);

                        return self::EXIT_CODE_ERROR;
                    }
                }
                $this->stdout("\nMigrated up successfully.\n", Console::FG_GREEN);
            }
        }
        return self::EXIT_CODE_NORMAL;
    }

    public function getMigrationPaths()
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

}
