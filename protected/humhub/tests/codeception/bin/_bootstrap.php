<?php
defined('YII_DEBUG') || define('YII_DEBUG', true);
defined('YII_ENV') || define('YII_ENV', 'test');

// fcgi doesn't have STDIN and STDOUT defined by default
defined('STDIN') || define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') || define('STDOUT', fopen('php://stdout', 'w'));

defined('YII_APP_BASE_PATH') || define('YII_APP_BASE_PATH', dirname(dirname(dirname(dirname(__DIR__)))));

require(YII_APP_BASE_PATH . '/vendor/autoload.php');
require(YII_APP_BASE_PATH . '/vendor/yiisoft/yii2/Yii.php');

Yii::setAlias('@tests', dirname(dirname(__DIR__)));
