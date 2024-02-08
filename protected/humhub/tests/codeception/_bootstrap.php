<?php

//Initialize Yii
use Codeception\Configuration;
use Codeception\Util\Autoload;

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');
defined('YII_ENV_TEST') or define('YII_ENV_TEST', true);

defined('YII_TEST_ENTRY_URL') or define('YII_TEST_ENTRY_URL', parse_url(Configuration::config()['config']['test_entry_url'], PHP_URL_PATH));
defined('YII_TEST_ENTRY_FILE') or define('YII_TEST_ENTRY_FILE', dirname(__DIR__, 4) . '/index-test.php');

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php');

$_SERVER['SCRIPT_FILENAME'] = YII_TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = YII_TEST_ENTRY_URL;

$_SERVER['SERVER_NAME'] = parse_url(Configuration::config()['config']['test_entry_url'], PHP_URL_HOST);
$_SERVER['SERVER_PORT'] = parse_url(Configuration::config()['config']['test_entry_url'], PHP_URL_PORT) ?: '80';

// Set alias
$config = Configuration::config();

$config['test_root'] = $config['test_root'] ?? dirname(__DIR__);
$config['humhub_root'] = $config['humhub_root'] ?? dirname(__DIR__, 4) . '/';

Yii::setAlias('@tests', $config['test_root']);
Yii::setAlias('@env', '@tests/config/env');
Yii::setAlias('@modules', dirname(__DIR__, 2) . '/modules');
Yii::setAlias('@root', $config['humhub_root']);
Yii::setAlias('@humhubTests', $config['humhub_root'] . '/protected/humhub/tests');
Yii::setAlias('@humhub', $config['humhub_root'] . '/protected/humhub');

Yii::setAlias('@web-static', '/static');
Yii::setAlias('@webroot-static', '@root/static');

// Load all supporting test classes needed for test execution
Autoload::addNamespace('', Yii::getAlias('@humhubTests/codeception/_support'));
Autoload::addNamespace('', Yii::getAlias('@tests/codeception/fixtures'));
Autoload::addNamespace('', Yii::getAlias('@humhubTests/codeception/fixtures'));
Autoload::addNamespace('', Yii::getAlias('@humhubTests/codeception/_pages'));
