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
 * ZCronRunner command is a generic cron interface.
 *
 * Third party modules can intercept run events to attach methods to the cron
 * subsystem.
 *
 * @package humhub.commands.shell.ZCron
 * @since 0.5
 */
class ZCronRunner extends HConsoleCommand {

    /**
     * Cron Intervals
     */
    const INTERVAL_DAILY = 1;
    const INTERVAL_HOURLY = 2;

    /**
     * @var integer current interval
     */
    public $interval = null;

    /**
     * Run method for CronRunner
     *
     * @param type $args
     */
    public function run($args) {

        $this->printHeader('Cron Interface');

        if (!isset($args[0]) || ($args[0] != "daily" && $args[0] != 'hourly')) {
            print "\n Run with parameter:\n" .
                    "\t daily - for Daily Interval\n" .
                    "\t hourly - for Hourly Interval \n";
            print "\n\n";
            exit;
        }

        // Set current interval to attribute
        if ($args[0] == 'daily')
            $this->interval = self::INTERVAL_DAILY;
        elseif ($args[0] == 'hourly')
            $this->interval = self::INTERVAL_HOURLY;
        else
            throw new CException(500, 'Invalid cron interval!');

        // Do some base tasks, handle by event in future
        if ($this->interval == self::INTERVAL_HOURLY) {

            // Raise Event for Module specific tasks
            if ($this->hasEventHandler('onHourlyRun'))
                $this->onHourlyRun(new CEvent($this));

            print "- Optimizing Search Index\n";
            // Optimize Search Index
            HSearch::getInstance()->Optimize();

            if (HSetting::Get('enabled', 'authentication_ldap') && HSetting::Get('refreshUsers', 'authentication_ldap')) {
                print "- Updating LDAP Users\n";
                HLdap::getInstance()->refreshUsers();
            }

            print "- Invoking EMailing hourly\n\n";
            // Execute Hourly Mailing Runner
            Yii::import('application.commands.shell.EMailing.*');
            $command = new EMailing('test', 'test');
            $command->run(array('hourly'));

            HSetting::Set('cronLastHourlyRun', time());
        } elseif ($this->interval == self::INTERVAL_DAILY) {
            // Raise Event for Module specific tasks

            if ($this->hasEventHandler('onDailyRun'))
                $this->onDailyRun(new CEvent($this));

            // Execute Daily Mailing Runner
            print "- Invoking EMailing daily\n\n";
            Yii::import('application.commands.shell.EMailing.*');
            $command = new EMailing('test', 'test');
            $command->run(array('daily'));

            HSetting::Set('cronLastDailyRun', time());
        }
    }

    /**
     * This event is raised after the cron hourly run is performed.
     *
     * @param CEvent $event the event parameter
     */
    public function onHourlyRun($event) {
        $this->raiseEvent('onHourlyRun', $event);
    }

    /**
     * This event is raised after the cron hourly run is performed.
     *
     * @param CEvent $event the event parameter
     */
    public function onDailyRun($event) {
        $this->raiseEvent('onDailyRun', $event);
    }

}
