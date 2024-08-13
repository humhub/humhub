<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * This file is executed before the _bootstrap file and loads the humhub test config
 * test.php by means of the env settings.
 */

use Codeception\Configuration;

$codeceptConfig = Configuration::config();

$testRoot = $codeceptConfig['test_root'];
$humhubRoot = $codeceptConfig['humhub_root'];

// Parse the environment arguments
$env = $GLOBALS['env'] ?? [];

// If an environment was set try loading special environment config else load default config
if (count($env) > 0) {
    Configuration::append(['environment' => $env]);

    /** @noinspection ForgottenDebugOutputInspection */
    print_r('Run execution environment: ' . $env[0]);

    $envCfgFile = $testRoot . '/config/env/' . $env[0] . '/test.php';

    if (file_exists($envCfgFile)) {
        $cfg = array_merge(require($testRoot . '/config/test.php'), require($envCfgFile));
    }
}

// If no environment is set we have to load the default config
if (!isset($cfg)) {
    $cfg = require($testRoot . '/config/test.php');
}

// We prefer the system environment setting over the configuration
if ($humhubRoot) {
    $cfg['humhub_root'] = $humhubRoot;
} else {
    // If no humhub_root is given we assume to be in /protected/humhub/modules/<module>/tests/codeception directory
    $cfg['humhub_root'] ??= $testRoot . '../../../../';
}

// Set some configurations and overwrite the humhub_root
if (isset($cfg['modules'])) {
    Configuration::append(['humhub_modules' => $cfg['modules']]);
}

if (isset($cfg['humhub_root'])) {
    Configuration::append(['humhub_root' => $cfg['humhub_root']]);
}

if (isset($cfg['fixtures'])) {
    Configuration::append(['fixtures' => $cfg['fixtures']]);
}

return $cfg;
