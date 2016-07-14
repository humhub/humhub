<?php

/**
 * Application configuration shared by all test types
 */
$default = [
    'name' => 'HumHub Test',
    'language' => 'en-US',
    'params' => [
        'installed' => true,
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\faker\FixtureController',
            'fixtureDataPath' => '@tests/codeception/fixtures',
            'templatePath' => '@tests/codeception/templates',
            'namespace' => 'tests\codeception\fixtures',
        ],
    ],
    'components' => [
        'mailer' => [
            'useFileTransport' => true,
        ],
        'urlManager' => [
            'showScriptName' => true,
            'scriptUrl' => 'index-test.php',
        ],
    ],
];

return yii\helpers\ArrayHelper::merge(
    // Default Test Config
    $default,
     // User Overwrite
    require(dirname(__DIR__).'/../config/common.php')
);
