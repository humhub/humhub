<?php

return [
    'components' => [
        'db' => [
            'dsn' => 'mysql:host=127.0.0.1;dbname=humhub_test',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'attributes' => [
                PDO::ATTR_PERSISTENT => true,
            ],
        ],
        'fs' => [
            'mounts' => [
                'assets' => [
                    'path' => '@webroot/assets/tests',
                    'baseUrl' => '@web/assets/tests',
                ],
                'data' => [
                    'path' => '@root/uploads/tests',
                ],
            ],
        ],
        'view' => [
            'theme'
            => [
                'name' => \humhub\components\Theme::CORE_THEME_NAME,
                'basePath' => '@humhub/themes/' . \humhub\components\Theme::CORE_THEME_NAME,
            ],
        ],
        'queue' => [
            'class' => 'humhub\modules\queue\driver\Instant',
        ],
        'urlManager' => [
            'class' => \humhub\components\console\UrlManager::class,
        ],
        'captcha' => [
            'class' => \humhub\components\captcha\YiiCaptcha::class,
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
            'cachePath' => '@runtime/tests/cache',
        ],
    ],
    'params' => [
        'installed' => true,
    ],
    'modules' => [
        'user' => [
            'loginRememberMeDefault' => false,
            'enableRegistrationFormCaptcha' => false,
        ],
    ],
];
