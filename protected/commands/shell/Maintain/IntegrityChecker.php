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
 * IntegrityChecker command validates the current database and tries to fix
 * currupted records.
 *
 * Also third party modules can add checking methods by intercepting the run
 * event.
 *
 * @package humhub.commands.shell.Maintain
 * @since 0.5
 */
class IntegrityChecker extends HConsoleCommand
{

    public $simulate = false;

    /**
     * Runs Integrity Checker
     *
     * @param type $args
     */
    public function run($args)
    {

        $this->printHeader('Integrity Checker');

        if ($this->simulate) {
            print "Simulation Mode!\n\n";
        }

        $this->doBaseTasks();

        if ($this->hasEventHandler('onRun'))
            $this->onRun(new CEvent($this));

        print "\n\n";
        print "Finished! Integrity Check done!\n\n";
    }

    /**
     * Do general tasks used application whide
     */
    protected function doBaseTasks()
    {

        $this->showTestHeadline("Checking application base structure");

        if (HSetting::Get('secret') == "" || HSetting::Get('secret') == null) {
            HSetting::Set('secret', UUID::v4());
            $this->showFix('Setting missing application secret!');
        }
    }

    /**
     * onRun Event is used to notify modules.
     *
     * @param type $event
     */
    public function onRun($event)
    {
        $this->raiseEvent('onRun', $event);
    }

    /**
     * Shows a headline in console
     * @param type $title
     */
    public function showTestHeadline($title)
    {
        print "*** " . $title . "\n";
    }

    /**
     * Use this method to show a fix
     * @param string $msg
     */
    public function showFix($msg)
    {
        print "\t" . $msg . "\n";
    }

    /**
     * Use this method to show a warning
     * 
     * @since 0.11.2
     * @param string $msg
     */
    public function showWarning($msg)
    {
        print "\tWARNING: " . $msg . "\n";
    }

}
