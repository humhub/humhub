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
$yii = dirname(__FILE__) . '/protected/vendors/yii/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';
$appClass = dirname(__FILE__) . '/protected/components/WebApplication.php';

// Disable these 3 lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 5);
ini_set('error_reporting', E_ALL);

require_once($yii);
require_once($appClass);

$app = Yii::createApplication('WebApplication', $config);

Yii::import('application.vendors.*');
EZendAutoloader::$prefixes = array('Zend', 'Custom');
Yii::import("ext.yiiext.components.zendAutoloader.EZendAutoloader", true);
Yii::registerAutoloader(array("EZendAutoloader", "loadClass"), true);

$app->run();
