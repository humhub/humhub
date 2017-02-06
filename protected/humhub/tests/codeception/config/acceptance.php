<?php

/**
 * Application configuration for acceptance tests
 */
$testConfig = [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'test'
        ],
    ],
    'params' => [
        'dynamicConfigFile' => '@humhub/tests/codeception/config/dynamic.php',
        'installed' => true,
        'settings' => [
            'core' => [
                'name' => 'HumHub Test',
                'baseUrl' => 'http://localhost:8080',
            ]
        ],
        'enablePjax' => true
    ],
    
];

defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', dirname(dirname(dirname(dirname(__DIR__)))));

return yii\helpers\ArrayHelper::merge(
                // Common Config
                require(YII_APP_BASE_PATH . '/humhub/config/common.php'),
                // Web Config
                require(YII_APP_BASE_PATH . '/humhub/config/web.php'),
                // Test Common Config
                require(__DIR__ . '/config.php'),
                // Acceptance Test Config
                $testConfig
);


