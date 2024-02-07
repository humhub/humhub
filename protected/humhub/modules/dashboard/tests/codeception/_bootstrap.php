<?php

/**
 * This is the initial test bootstrap, which will load the default test bootstrap from the humhub core
 */

// Parse the environment arguments (Note: only simple --env ENV is supported no comma sepration merge...)
use Codeception\Configuration;
use Codeception\Util\Autoload;

$env = isset($GLOBALS['env']) ? $GLOBALS['env'] : [];

// If environment was set try loading special environment config else load default
if (count($env) > 0) {
    Configuration::append(['environment' => $env]);


    $envCfgFile = dirname(__DIR__) . '/config/env/test.' . $env[0][0] . '.php';

    if (file_exists($envCfgFile)) {
        $cfg = array_merge(require_once(__DIR__ . '/../config/test.php'), require_once($envCfgFile));
    }
}

// If no environment is set we have to load the default config
if (!isset($cfg)) {
    $cfg = require_once(__DIR__ . '/../config/test.php');
}

// If no humhub_root is given we assume our module is in the a root to be in /protected/humhub/modules/<module>/tests/codeception directory
$cfg['humhub_root'] = isset($cfg['humhub_root']) ? $cfg['humhub_root'] : dirname(__DIR__) . '/../../../../..';


// Load default test bootstrap
require_once($cfg['humhub_root'] . '/protected/humhub/tests/codeception/_bootstrap.php');

// Overwrite the default test alias
Yii::setAlias('@tests', dirname(__DIR__));
Yii::setAlias('@env', '@tests/config/env');
Yii::setAlias('@root', $cfg['humhub_root']);
Yii::setAlias('@humhubTests', $cfg['humhub_root'] . '/protected/humhub/tests');

// Load all supporting test classes needed for test execution
Autoload::addNamespace('', Yii::getAlias('@humhubTests/codeception/_support'));
Autoload::addNamespace('tests\codeception\fixtures', Yii::getAlias('@humhubTests/codeception/fixtures'));
Autoload::addNamespace('', Yii::getAlias('@humhubTests/codeception/_pages'));
if (isset($cfg['modules'])) {
    Configuration::append(['humhub_modules' => $cfg['modules']]);
}

if (isset($cfg['fixtures'])) {
    Configuration::append(['fixtures' => $cfg['fixtures']]);
}
