<?php

return [
    'components' => [
        'response' => [
            'defaultHeaders' => [
                'Strict-Transport-Security' => 'max-age=31536000',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'deny',
                'Referrer-Policy' => 'no-referrer-when-downgrade',
                'X-Permitted-Cross-Domain-Policies' => 'master-only',
                'My-Custom-Security-Header' => 'test',
                'Content-Security-Policy' => "default-src 'self'; script-src {{ nonce }} 'self'; report-uri {{ reportUri }}",
            ],
        ],
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
                    'path' => '@webroot/uploads/tests',
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
