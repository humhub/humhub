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
                'yii\web\JqueryAsset' => [
                    'sourcePath' => '@npm/jquery/dist',
                ],
            ],
        ],
        'request' => [
            'class' => \humhub\components\Request::class,
        ],
        'response' => [
            'class' => \humhub\components\Response::class,
        ],
        'user' => [
            'class' => \humhub\modules\user\components\User::class,
            'identityClass' => \humhub\modules\user\models\User::class,
            'enableAutoLogin' => true,
            'authTimeout' => 1400,
            'loginUrl' => ['/user/auth/login']
        ],
        'errorHandler' => [
            'errorAction' => '/error/index',
        ],
        'session' => [
            'class' => \humhub\modules\user\components\Session::class,
        ],
    ],
    'modules' => [
        'web' => [
            'security' =>  [
                "headers" => [
                    "Strict-Transport-Security" => "max-age=31536000",
                    "X-XSS-Protection" => "1; mode=block",
                    "X-Content-Type-Options" => "nosniff",
                    "Referrer-Policy" => "no-referrer-when-downgrade",
                    "X-Permitted-Cross-Domain-Policies" => "master-only",
                    "X-Frame-Options" => "sameorigin"
                ],
                "csp" => [
                    "nonce" => false,
                    "report-only" => false,
                    "report" => false,
                    "default-src" => [
                        "self" => true
                    ],
                    "img-src" => [
                        "data"=> true,
                        "allow" => [
                            "*" ,
                        ],
                    ],
                    "font-src" => [
                        "self" => true
                    ],
                    "style-src" => [
                        "self" => true,
                        "unsafe-inline" => true,
                        "allow" => [
                            "*" ,
                        ],
                    ],
                    "object-src" => [
                    ],
                    "frame-src" => [
                        "allow" => [
                            "*"
                        ]
                    ],
                    "script-src" => [
                        "self" => true,
                        "unsafe-inline" => true,
                        "unsafe-eval" => false,
                        "report-sample" => true,
                        "allow" => [
                            "*" ,
                        ],
                    ],
                    "upgrade-insecure-requests" => false
                ]
            ]
        ]
    ],
];

return $config;
