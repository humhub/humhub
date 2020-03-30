<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\assets\AppAsset;
use humhub\assets\CoreBundleAsset;
use humhub\assets\JuiBootstrapBridgeAsset;
use humhub\components\assets\AssetBundle;
use humhub\components\assets\WebStaticAssetBundle;
use humhub\modules\ui\view\components\View;
use yii\bootstrap\BootstrapAsset;
use yii\bootstrap\BootstrapPluginAsset;
use yii\helpers\ArrayHelper;
use yii\web\JqueryAsset;

/**
 * Configuration file for the "yii asset" console command.
 */

// In the console environment, some path aliases may not exist. Please define these:
Yii::setAlias('@webroot', __DIR__ . '/../../../');
Yii::setAlias('@web', '/');

Yii::setAlias('@webroot-static', __DIR__ . '/../../../static');
Yii::setAlias('@web-static', '/static');

$bundels = ArrayHelper::merge(AppAsset::STATIC_DEPENDS, CoreBundleAsset::STATIC_DEPENDS);
$bundels = ArrayHelper::merge([AppAsset::class, CoreBundleAsset::class], $bundels);

return [
    // Adjust command/callback for JavaScript files compressing:
    'jsCompressor' => 'grunt uglify:assets  --from={from} --to={to} -d',
    // Adjust command/callback for CSS files compressing:
    'cssCompressor' => 'grunt cssmin --from={from} --to={to}',
    // The list of asset bundles to compress:
    'bundles' => $bundels,
    // Asset bundle for compression output:
    'targets' => [
        AppAsset::BUNDLE_NAME => [
            'class' => WebStaticAssetBundle::class,
            'defer' => false,
            'defaultDepends' => false,
            'basePath' => '@webroot-static',
            'baseUrl' => '@web-static',
            'jsPosition' => View::POS_HEAD,
            'js' => 'js/humhub-app.js',
            'css' => 'css/humhub-app.css',
            'preload' => [
                'js/humhub-app.js',
                'css/humhub-app.css',
            ],
            'depends' => AppAsset::STATIC_DEPENDS
        ],
        CoreBundleAsset::BUNDLE_NAME => [
            'class' => WebStaticAssetBundle::class,
            'defer' => true,
            'jsPosition' => View::POS_HEAD,
            'defaultDepends' => false,
            'basePath' => '@webroot-static',
            'baseUrl' => '@web-static',
            'js' => 'js/humhub-bundle.js',
            'css' => 'css/humhub-bundle.css',
            'preload' => [
                'js/core-bundle.js',
                'css/core-bundle.css',
            ],
            'depends' => CoreBundleAsset::STATIC_DEPENDS,
        ],
    ],
    'assetManager' => [
        'basePath' => '@webroot-static/assets',
        'baseUrl' => '@web-static/assets',
        'bundles' => [
            JqueryAsset::class => [
                'sourcePath' => '@npm/jquery/dist'
            ],
            BootstrapPluginAsset::class => [
                'js' => ['js/bootstrap.min.js'],
                'depends' => [
                    JqueryAsset::class,
                    BootstrapAsset::class,
                    JuiBootstrapBridgeAsset::class
                ]
            ],

        ]
    ],
];
