<?php

/**
 * Application configuration shared by all test types
 */
return [
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
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=humhub_test',
            'username' => 'travis',
            'password' => '',
            'charset' => 'utf8',
        ], 
        'mailer' => [
            'useFileTransport' => true,
        ],
        'urlManager' => [
            'showScriptName' => true,
            'scriptUrl' => 'index-test.php',
        ],
    ],
];
