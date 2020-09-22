<?php

/**
 * Application configuration for unit tests
 */
$testConfig = [
    'class' => 'humhub\components\Application',
    'timeZone' => 'UTC',
    'components' => [
        'cache' => [
            'class' => \yii\caching\DummyCache::class,
        ],
        'session' => [
            'class' => \yii\web\CacheSession::class,
        ],
        'request' => [
            'cookieValidationKey' => 'test'
        ],
        'user' => [
        	'enableSession' => false
        ],
        'assetManager' => [
            'basePath' => '@root/assets/'
        ]
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
