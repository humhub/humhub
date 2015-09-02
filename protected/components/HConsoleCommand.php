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
 * HConsoleCommand extends the default CConsoleCommand.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class HConsoleCommand extends CConsoleCommand
{

    /**
     * Inits the command and prepares the base environment.
     */
    public function init()
    {

        Yii::app()->interceptor->intercept($this);

        Yii::import('application.vendors.*');

        EZendAutoloader::$prefixes = array('Zend', 'Custom');
        Yii::import("ext.yiiext.components.zendAutoloader.EZendAutoloader", true);
        Yii::registerAutoloader(array("EZendAutoloader", "loadClass"), true);

        ini_set('max_execution_time', 9000);
        ini_set('memory_limit', '1024M');
        date_default_timezone_set("Europe/Berlin");

        error_reporting(E_ALL ^ E_NOTICE);

        #HSetting::InstallBase();
    }

    /**
     * Prints out the console application header
     *
     * @param String $title is the title of the command
     */
    protected function printHeader($title)
    {

        print "


                     HumHub Console Interface - Version 1

--------------------------------------------------------------------------------
$title
--------------------------------------------------------------------------------\n
\n";
    }

    protected function printLine()
    {
        print "\n--------------------------------------------------------------------------------\n";
    }

}

?>
