<?php

/**
 * Application configuration for unit tests
 */
$testConfig = [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'test'
        ],
        'user' => [
        	'enableSession' => false
        ],
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
                // Unit Test Config
                $testConfig
);

