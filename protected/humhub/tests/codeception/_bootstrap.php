<?php

//Initialize Yii
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

defined('YII_TEST_ENTRY_URL') or define('YII_TEST_ENTRY_URL', parse_url(\Codeception\Configuration::config()['config']['test_entry_url'], PHP_URL_PATH));
defined('YII_TEST_ENTRY_FILE') or define('YII_TEST_ENTRY_FILE', dirname(dirname(dirname(dirname(__DIR__)))) . '/index-test.php');

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php');

$_SERVER['SCRIPT_FILENAME'] = YII_TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = YII_TEST_ENTRY_URL;

$_SERVER['SERVER_NAME'] = parse_url(\Codeception\Configuration::config()['config']['test_entry_url'], PHP_URL_HOST);
$_SERVER['SERVER_PORT'] = parse_url(\Codeception\Configuration::config()['config']['test_entry_url'], PHP_URL_PORT) ? : '80';


// Set alias
$config = \Codeception\Configuration::config();
$testRoot = $config['test_root'];

if($testRoot == null) {
    $testRoot = dirname(__DIR__);
}

Yii::setAlias('@tests', $testRoot);
Yii::setAlias('@env', '@tests/config/env');
Yii::setAlias('@modules', dirname(dirname(__DIR__)).'/modules');
Yii::setAlias('@root', $config['humhub_root']);
Yii::setAlias('@humhubTests', $config['humhub_root'] . '/protected/humhub/tests');

// Load all supporting test classes needed for test execution 
\Codeception\Util\Autoload::addNamespace('', Yii::getAlias('@humhubTests/codeception/_support'));
\Codeception\Util\Autoload::addNamespace('tests\codeception\fixtures', Yii::getAlias('@humhubTests/codeception/fixtures'));
\Codeception\Util\Autoload::addNamespace('', Yii::getAlias('@humhubTests/codeception/_pages'));