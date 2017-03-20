<?php

$_SERVER['SCRIPT_FILENAME'] = YII_TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = YII_TEST_ENTRY_URL;

/**
 * Application configuration for functional tests
 */
$testConfig = [
    'class' => 'humhub\components\Application',
    'components' => [
        'request' => [
            // it's not recommended to run functional tests with CSRF validation enabled
            'enableCsrfValidation' => false,
            'cookieValidationKey' => 'test'
        // but if you absolutely need it set cookie domain to localhost
        /*
          'csrfCookie' => [
          'domain' => 'localhost',
          ],
         */
        ],
        'user' => [
            'enableAutoLogin' => true
        ],
        // Default ErrorAction results in 'Unable to resolve the request "error/index" exception
        'errorHandler' => [
            'errorAction' => null,
            'maxSourceLines' => 20,
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
                require(dirname(__DIR__) . '/config/config.php'),
                // Functional Test Config
                $testConfig
);
