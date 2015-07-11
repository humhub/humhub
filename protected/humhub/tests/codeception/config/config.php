<?php

/**
 * Application configuration shared by all test types
 */
defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', dirname(dirname(dirname(dirname(__DIR__)))));

$testConfig = [
    'language' => 'en-US',
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\faker\FixtureController',
            'fixtureDataPath' => '@tests/codeception/fixtures',
            'templatePath' => '@tests/codeception/templates',
            'namespace' => 'tests\codeception\fixtures',
        ],
    ],
    'basePath' => '/projects2/humhub/humhub2/',
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=humhub2',
        ],
        'mailer' => [
            'useFileTransport' => true,
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
    ],
];

return yii\helpers\ArrayHelper::merge(
                // Common
                require(YII_APP_BASE_PATH . '/humhub/config/common.php'),
                // HumHub Web Config
                require(YII_APP_BASE_PATH . '/humhub/config/web.php'),
                // Custom Web Config
                require(YII_APP_BASE_PATH . '/config/web.php'),
                // Test Config
                $testConfig
);

