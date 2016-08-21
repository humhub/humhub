<?php
/**
 * This is the initial test bootstrap, which will load the default test bootstrap from the humhub core
 */

$testRoot = dirname(__DIR__);
\Codeception\Configuration::append(['test_root' => $testRoot]);

$humhubPath = getenv("HUMHUB_PATH");

// If no environment path was set, we assume residing in default the modules directory
if($humhubPath == null) {
    $testCfg = require_once($testRoot.'/config/test.php');
    if(isset($testCfg['humhub_root'])) {
        $humhubPath = $testCfg['humhub_root'];
    } else {
        $humhubPath = dirname(__DIR__).'../../../../';
    }
}

\Codeception\Configuration::append(['humhub_root' => $humhubPath]);

// Load test configuration (/config/test.php or /config/env/<environment>/test.php
$cfg = require($humhubPath . '/protected/humhub/tests/codeception/_loadConfig.php');

print_r('Using HumHub Root: ' . $cfg['humhub_root']);

// Load default test bootstrap (initialize Yii...)
require_once($cfg['humhub_root'] . '/protected/humhub/tests/codeception/_bootstrap.php');