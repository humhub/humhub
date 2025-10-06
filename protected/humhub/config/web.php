<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

$config = [
    'id' => 'humhub',
    'bootstrap' => ['humhub\components\bootstrap\LanguageSelector'],
    'defaultRoute' => '/home',
    'layoutPath' => '@humhub/views/layouts',
    'components' => [
        'assetManager' => [
            'bundles' => [
                // Override obsolete @bower packages to @npm
                'yii\web\JqueryAsset' => [
                    'sourcePath' => '@npm/jquery/dist',
                ],
                'yii\jui\JuiAsset' => [
                    'sourcePath' => '@npm/jquery-ui/dist',
                ],
                'yii\bootstrap5\BootstrapAsset' => [
                    'sourcePath' => '@npm/bootstrap/dist',
                ],
                'yii\bootstrap5\BootstrapPluginAsset' => [
                    'sourcePath' => '@npm/bootstrap/dist',
                ],
                'yii\jui\DatePickerLanguageAsset' => [
                    'sourcePath' => '@npm/jquery-ui',
                ],
            ],
        ],
        'request' => [
            'class' => \humhub\components\Request::class,
            'csrfCookie' => [
                'sameSite' => yii\web\Cookie::SAME_SITE_LAX,
            ],
        ],
        'response' => [
            'class' => \humhub\components\Response::class,
        ],
        'captcha' => [
            'class' => \humhub\components\captcha\AltchaCaptcha::class,
            // 'class' => \humhub\components\captcha\YiiCaptcha::class,
        ],
        'user' => [
            'class' => \humhub\modules\user\components\User::class,
            'identityClass' => \humhub\modules\user\models\User::class,
            'enableAutoLogin' => true,
            'authTimeout' => 1400,
            'loginUrl' => ['/user/auth/login'],
            'identityCookie' => [
                'name' => '_identity',
                'sameSite' => yii\web\Cookie::SAME_SITE_LAX,
            ],
        ],
        'errorHandler' => [
            'errorAction' => '/error/index',
        ],
        'session' => [
            'class' => \humhub\modules\user\components\Session::class,
            'cookieParams' => [
                'httpOnly' => true,
                'sameSite' => yii\web\Cookie::SAME_SITE_LAX,
            ],
        ],
    ],
    'modules' => [
        'web' => [
            'security' => [
                "headers" => [
                    "Strict-Transport-Security" => "max-age=31536000",
                    "X-Content-Type-Options" => "nosniff",
                    "Referrer-Policy" => "no-referrer-when-downgrade",
                    "X-Permitted-Cross-Domain-Policies" => "master-only",
                    "X-Frame-Options" => "sameorigin",
                    "Content-Security-Policy" => "default-src *; connect-src  *; font-src 'self'; frame-src https://* http://* *; img-src https://* http://* * data:; object-src 'self'; script-src {{ nonce }} 'self' https://* http://* * 'unsafe-inline' 'report-sample'; style-src * https://* http://* * 'unsafe-inline'; block-all-mixed-content;",
                ],
                'csp' => [
                    'nonce' => true,
                ],
            ],
        ],
    ],
    'container' => [
        'definitions' => [
            \yii\web\Cookie::class => \humhub\libs\CookieBuilder::build(...),
            \yii\widgets\LinkPager::class => \humhub\widgets\bootstrap\LinkPager::class,
        ],
    ],
];

return $config;
