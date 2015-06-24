<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ZDbMigration.php');
Yii::import('application.extensions.migrate-command.EMigrateCommand');

/**
 * ZMigrateCommand extends EMigrateCommand with better interactive support.
 *
 * @package humhub.commands
 * @since 0.5
 */
class ZMigrateCommand extends EMigrateCommand
{

    public $migrationTable = 'migration';

    public static function AutoMigrate()
    {


        /**        $commandPath = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'commands';
          $runner->addCommands($commandPath);
          $commandPath = Yii::getFrameworkPath() . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'commands';
          $runner->addCommands($commandPath); */
        $runner = new CConsoleCommandRunner();

        $runner->commands = array(
            'migrate' => array(
                'class' => 'applications.commands.shell.ZMigrateCommand',
                'interactive' => false,
            ),
        );

        $args = array('yiic', 'migrate', '--interactive=0');
        ob_start();
        $runner->run($args);

        Yii::app()->db->schema->refresh();

        return htmlentities(ob_get_clean(), null, Yii::app()->charset);
    }

    public function init()
    {

        print "Flushing Caches....\n";
        Yii::app()->cache->flush();
        ModuleManager::flushCache();

        print "Autodetecting Modules....\n";




        $modulePaths = array();
        foreach (Yii::app()->moduleManager->getInstalledModules(true, true) as $moduleId => $classAlias) {

            // Convert path.to.example.ExampleModule to path.to.example.migrations
            $path = explode(".", $classAlias);
            array_pop($path);
            $path[] = $this->migrationSubPath;
            $migrationPath = implode(".", $path);

            if (is_dir(Yii::getPathOfAlias($migrationPath))) {
                $modulePaths[$moduleId] = $migrationPath;
            }
        }

        $this->modulePaths = $modulePaths;
    }

    protected function instantiateMigration($class)
    {

        $migration = new $class;
        $migration->setDbConnection($this->getDbConnection());
        if ($migration instanceof EDbMigration) {
            /** @var EDbMigration $migration */
            $migration->setCommand($this);
        }
        return $migration;
    }

    protected function getMigrationHistory($limit)
    {
        $db = $this->getDbConnection();
        
        $db->schema->refresh();
        
        // Checking for migration table without throwing an error
        if (!array_key_exists($this->migrationTable, $db->schema->getTables())) {
            
            echo 'Creating migration history table "' . $this->migrationTable . '"...';
            $db->createCommand()->createTable($this->migrationTable, array(
                'version' => 'string NOT NULL PRIMARY KEY',
                'apply_time' => 'integer',
                'module' => 'VARCHAR(32)',
            ));
            echo "done.\n";
            
            
        }
        
        return parent::getMigrationHistory($limit);
    }

    public function run($args)
    {

        $exitCode = parent::run($args);

        Yii::app()->cache->flush();
        ModuleManager::flushCache();
    }

    protected function getTemplate()
    {
        if ($this->templateFile !== null) {
            return parent::getTemplate();
        } else {
            return str_replace('CDbMigration', 'ZDbMigration', parent::getTemplate());
        }
    }

}
