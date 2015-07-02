<?php

$config = [
    'id' => 'humhub',
    'bootstrap' => ['humhub\components\bootstrap\LanguageSelector'],
    'defaultRoute' => '/dashboard/dashboard',
    'layoutPath' => '@humhub/views/layouts',
    'components' => [
        'request' => [
            'class' => 'humhub\components\Request',
            'cookieValidationKey' => 'asdf',
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
        'session' => [
            'class' => 'humhub\core\user\components\Session',
            'sessionTable' => 'user_http_session',
        ],
    ],
    'modules' => [],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
