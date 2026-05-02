<?php

/**
 * Bootstrap for LDAP module tests.
 * Loads the HumHub core bootstrap and re-sets the @tests alias to point
 * to this module's tests directory.
 */

use Codeception\Configuration;
use Codeception\Util\Autoload;

$env = $GLOBALS['env'] ?? [];

if (count($env) > 0) {
    Configuration::append(['environment' => $env]);

    $envCfgFile = dirname(__DIR__) . '/config/env/test.' . $env[0][0] . '.php';
    if (file_exists($envCfgFile)) {
        $cfg = array_merge(require_once(__DIR__ . '/../config/test.php'), require_once($envCfgFile));
    }
}

if (!isset($cfg)) {
    $cfg = require_once(__DIR__ . '/../config/test.php');
}

// Fall back to the standard location of humhub_root relative to this file:
// protected/humhub/modules/ldap/tests/codeception  =>  ../../../../.. = repo root/protected
$cfg['humhub_root'] ??= dirname(__DIR__) . '/../../../../..';

// Load the shared HumHub test bootstrap
require_once($cfg['humhub_root'] . '/protected/humhub/tests/codeception/_bootstrap.php');

// Override @tests to point to this module's tests directory
Yii::setAlias('@tests', dirname(__DIR__));
Yii::setAlias('@env', '@tests/config/env');
Yii::setAlias('@root', $cfg['humhub_root']);
Yii::setAlias('@humhubTests', $cfg['humhub_root'] . '/protected/humhub/tests');

Autoload::addNamespace('', Yii::getAlias('@humhubTests/codeception/_support'));
Autoload::addNamespace('tests\codeception\fixtures', Yii::getAlias('@humhubTests/codeception/fixtures'));
Autoload::addNamespace('', Yii::getAlias('@humhubTests/codeception/_pages'));
