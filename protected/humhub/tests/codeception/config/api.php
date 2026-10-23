<?php

/**
 * Application configuration for api tests
 */
$testConfig = [
    'class' => 'humhub\components\Application',
    'components' => [
        'urlManager' => [
            'showScriptName' => false,
            'enablePrettyUrl' => true,
        ],
        // Counters per test instead of the cache, which this suite never flushes
        'rateLimiter' => [
            'storage' => ['class' => \tests\codeception\_support\MemoryRateLimitStorage::class],
        ],
    ],
];

defined('YII_APP_BASE_PATH') or define('YII_APP_BASE_PATH', dirname(__DIR__, 4));

return yii\helpers\ArrayHelper::merge(
    // Common Config
    require(YII_APP_BASE_PATH . '/humhub/config/common.php'),
    // Web Config
    require(YII_APP_BASE_PATH . '/humhub/config/web.php'),
    // Test Common Config
    require(__DIR__ . '/config.php'),
    // API Test Config
    $testConfig,
);
