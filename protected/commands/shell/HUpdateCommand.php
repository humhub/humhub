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

/**
 * UpdateCommand should be executed after replacing HumHub Source files.
 * It basically runs migrations and may do further checks (module compatiblity)
 * in future.
 * 
 * @author luke
 */
class HUpdateCommand extends HConsoleCommand
{

    /**
     * Interactive mode
     * 
     * @var boolean
     */
    public $interactive = 1;

    /**
     * Got errors during update
     * 
     * @var boolean
     */
    protected $hasErrors = false;

    public function init()
    {
        $this->printHeader('Updater');
        return parent::init();
    }

    public function actionIndex($args, $interactive = null)
    {
        
        if ($interactive != null) {
            $this->interactive = $interactive;
        }

        print "*** Begin Migrations ***\n\n";
        $this->runMigrations();
        $this->printLine();

        print "\n*** Checking Modules ***\n\n";
        $this->runModuleCheck();

        print "\n\n\n";
        $this->printLine();
        if (!$this->hasErrors) {
            print "Finished succesfully without errors!";
        } else {
            print "Finished with ERRORS!";
        }
        $this->printLine();
        print "\n";

        Notification::model()->deleteAllByAttributes(array('class' => 'HumHubUpdateNotification'));
    }

    /**
     * Runs migration command
     */
    protected function runMigrations()
    {

        $runner = new CConsoleCommandRunner();

        $runner->commands = array(
            'migrate' => array(
                'class' => 'application.commands.shell.ZMigrateCommand',
            ),
        );
        $args = array('yiic', 'migrate', '--interactive=' . $this->interactive);
        $runner->run($args);
    }

    /**
     * Checks if installed module compatiblity / try load them.
     */
    protected function runModuleCheck()
    {
        // TODO
    }

    /**
     * Shows confirmation dialog
     * 
     * @param String $message
     * @param String $default
     * @return boolean
     */
    public function confirm($message, $default = false)
    {
        if (!$this->interactive)
            return true;
        return parent::confirm($message, $default);
    }

    /**
     * Shows help message
     * 
     * @return String
     */
    public function getHelp()
    {
        return <<<EOD
USAGE
  yiic update [--interactive=0]

DESCRIPTION
  Run this command after updating HumHub Core files.

EXAMPLES
 * yiic update

EOD;
    }

    public static function AutoUpdate()
    {
        $runner = new CConsoleCommandRunner();
        $runner->commands = array(
            'update' => array(
                'class' => 'applications.commands.shell.HUpdateCommand',
                'interactive' => false,
            ),
        );

        $args = array('yiic', 'update', '--interactive=0');
        ob_start();
        $runner->run($args);

        Yii::app()->db->schema->refresh();

        return htmlentities(ob_get_clean(), null, Yii::app()->charset);
    }

}
