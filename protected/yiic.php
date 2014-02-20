<?php
/*
// change the following paths if necessary
$yiic=dirname(__FILE__).'/vendors/yii/yiic.php';
$config=dirname(__FILE__).'/config/console.php';

require_once($yiic);
*/


$config=dirname(__FILE__).'/config/console.php';

defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('YII_DEBUG') or define('YII_DEBUG',true);

require_once(dirname(__FILE__).'/vendors/yii/yii.php');
require_once(dirname(__FILE__).'/components/HConsoleApplication.php');

$app=Yii::createApplication('HConsoleApplication', $config);

Yii::setPathOfAlias('webroot', realpath(dirname(__FILE__). '/..'));

// Add Yii Commands
$app->commandRunner->addCommands(YII_PATH.'/cli/commands');

$app->run();
