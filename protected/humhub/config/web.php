<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

$config = [
    'id' => 'humhub',
    'bootstrap' => ['humhub\components\bootstrap\LanguageSelector'],
    'homeUrl' => ['/dashboard/dashboard'],
    'defaultRoute' => '/home',
    'layoutPath' => '@humhub/views/layouts',
    'components' => [
        'request' => [
            'class' => humhub\components\Request::class,
        ],
        'response' => [
            'class' => humhub\components\Response::class,
        ],
        'user' => [
            'class' => humhub\modules\user\components\User::class,
            'identityClass' => humhub\modules\user\models\User::class,
            'enableAutoLogin' => true,
            'authTimeout' => 1400,
            'loginUrl' => ['/user/auth/login']
        ],
        'errorHandler' => [
            'errorAction' => '/error/index',
        ],
        'session' => [
            'class' => humhub\modules\user\components\Session::class,
        ],
    ],
    'modules' => [],
];

return $config;
