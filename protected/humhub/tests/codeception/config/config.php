<?php

/**
 * Application configuration shared by all test types
 */
$default = [
    'name' => 'HumHub Test',
    'language' => 'en-US',
    'params' => [
        'installed' => true,
        'settings' => [
            'core' => [
                'name' => 'HumHub Test',
                'baseUrl' => 'http://localhost:8080',
            ],
        ]
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
            'messageClass' => \yii\symfonymailer\Message::class,
        ],
        'urlManager' => [
            'showScriptName' => true,
            'scriptUrl' => '/index-test.php',
        ],
    ],
    'modules' => [
        'user' => [
            'passwordStrength' => [
                '/^(.*?[A-Z]){2,}.*$/' => 'Password has to contain two uppercase letters.',
                '/^.{8,}$/' => 'Password needs to be at least 8 characters long.',
            ]
        ]
    ]
];

$envCfg = dirname(__DIR__) . '/../config/env/env.php';
$env = file_exists($envCfg) ? require($envCfg) : [];

return yii\helpers\ArrayHelper::merge(
// Default Test Config
    $default,
    // User Overwrite
    require(dirname(__DIR__) . '/../config/common.php'),
    $env
);
