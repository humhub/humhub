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
                    'publishOptions' => [
                        'only' => [
                            'jquery.js',
                        ],
                    ],
                ],
                'yii\jui\JuiAsset' => [
                    'sourcePath' => '@npm/jquery-ui/dist',
                    'publishOptions' => [
                        'only' => [
                            'jquery-ui.js',
                            'themes/smoothness/*',
                            'themes/smoothness/images/*',
                        ],
                    ],
                ],
                'yii\jui\DatePickerLanguageAsset' => [
                    'sourcePath' => '@npm/jquery-ui',
                    // Must stay identical to humhub\assets\JqueryWidgetAsset::$publishOptions,
                    // both bundles publish the same source path.
                    'publishOptions' => [
                        'only' => [
                            'ui/widget.js',
                            'ui/i18n/*',
                        ],
                    ],
                ],
                // Serve a single Select2 copy. Krajee widgets (e.g. the IconPicker, which
                // extends kartik\select2\Select2) publish their own Select2 build from the
                // select2/select2 package required by kartik-v/yii2-widget-select2, next to
                // the core one from npm-asset/select2. Loading a second build replaces
                // `$.fn.select2.amd` while `$.fn.select2` keeps running the first one, so
                // anything resolved from that AMD registry belongs to a foreign Select2
                // version. Emptying the Krajee bundle - through the values Krajee reserves
                // for exactly this purpose - and depending on the core bundle instead also
                // saves the duplicate download.
                'kartik\select2\Select2Asset' => [
                    // The source path must stay resolvable: kartik\select2\Select2 appends its
                    // own `js/i18n/<language>.js` to this bundle after registering it, so point
                    // it at the core Select2 package and publish nothing but those language
                    // files. `EMPTY_ASSET` is the value Krajee reserves for emptying one of its
                    // asset lists from the asset manager configuration.
                    'sourcePath' => '@npm/select2/dist',
                    'publishOptions' => [
                        'only' => [
                            'js/i18n/*',
                        ],
                    ],
                    'js' => \kartik\base\BaseAssetBundle::EMPTY_ASSET,
                    'css' => \kartik\base\BaseAssetBundle::EMPTY_ASSET,
                    'depends' => [
                        'yii\web\YiiAsset',
                        \humhub\assets\Select2Asset::class,
                    ],
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
                    "Content-Security-Policy" => "default-src *; connect-src  *; font-src 'self' https://* http://* *; frame-src https://* http://* *; img-src https://* http://* * data:; object-src 'self'; script-src {{ nonce }} 'self' https://* http://* * 'unsafe-inline' 'report-sample'; style-src * https://* http://* * 'unsafe-inline'; block-all-mixed-content;",
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
