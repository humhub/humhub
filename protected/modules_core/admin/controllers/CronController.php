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
 * Description of CronController
 *
 * @author luke
 */
class CronController extends Controller
{

    const MODE_HOURLY = 'hourly';
    const MODE_DAILY = 'daily';

    protected function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    public function actionHourly()
    {
        print "<pre>";
        print $this->runCron(self::MODE_HOURLY);
        echo "</pre>";
    }

    public function actionDaily()
    {
        echo "<pre>";
        echo $this->runCron(self::MODE_DAILY);
        echo "</pre>";
    }

    private function runCron($mode = self::MODE_HOURLY)
    {
        $runner = new CConsoleCommandRunner();
        $runner->commands = array(
            'cron' => 'application.commands.shell.ZCron.ZCronRunner'
        );

        ob_start();
        $runner->run(array('yiic', 'cron', $mode));
        return ob_get_clean();
    }

}
