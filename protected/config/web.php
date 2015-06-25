<?php

$params = require(__DIR__ . '/params.php');


$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'humhub\components\bootstrap\ModuleAutoLoader', 'humhub\components\bootstrap\LanguageSelector'],
    'defaultRoute' => '/dashboard/dashboard',
    'layoutPath' => '@humhub/views/layouts',
    'components' => [
        'request' => [
            'class' => 'humhub\components\Request',
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'SjYK1aWU5Z8WZwtd9jcQ0zL3KR_PJL0k',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'class' => 'humhub\core\user\components\User',
            'identityClass' => 'humhub\core\user\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['/user/auth/login']
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
            'viewPath' => '@humhub/views/mail',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\DbTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'i18n' => [
            'class' => 'humhub\components\i18n\I18N',
            'translations' => [
                'base' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@humhub/messages'
                ],
            ],
        ],
        'moduleManager' => [
            'class' => '\humhub\components\ModuleManager'
        ],
        'db' => require(__DIR__ . '/db.php'),
        'view' => [
            'class' => '\humhub\components\View',
            'theme' => [
                'basePath' => '@webroot/themes/HumHub',
                'baseUrl' => '@web/themes/HumHub',
                'pathMap' => [
                    '@app/views' => '@webroot/themes/HumHub/views',
                ],
            ],
        ],
    ],
    'modules' => [],
    'params' => $params,
];

if (YII_ENV_DEV) {
// configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
