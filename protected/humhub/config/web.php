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
            'class' => 'humhub\components\Request',
            'csrfCookie' => ['httpOnly' => true, 'secure' => true],
        ],
        'response' => [
            'class' => 'humhub\components\Response',
        ],
        'user' => [
            'class' => 'humhub\modules\user\components\User',
            'identityClass' => 'humhub\modules\user\models\User',
            'identityCookie' => ['name' => '_identity', 'httpOnly' => true, 'secure' => true],
            'enableAutoLogin' => true,
            'authTimeout' => 1400,
            'loginUrl' => ['/user/auth/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'error/index',
        ],
        'session' => [
            'class' => 'humhub\modules\user\components\Session',
            'sessionTable' => 'user_http_session',
            'cookieParams' => ['httpOnly' => true, 'secure' => true],
        ],
    ],
    'modules' => [],
];

return $config;
