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
use humhub\components\View;
use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap5\BootstrapPluginAsset;
use yii\helpers\ArrayHelper;
use yii\jui\DatePickerLanguageAsset;
use yii\jui\JuiAsset;
use yii\web\JqueryAsset;

/**
 * Configuration file for the "yii asset" console command.
 *
 * The build publishes every external dependency into `@humhub/resources/build/<hash>/`
 * and writes the compressed bundles into `@humhub/resources/{js,css}/`, so the
 * whole `@humhub/resources` tree becomes a self-contained, source-controlled
 * deployment unit. At runtime AssetManager publishes that tree to the assets
 * mount; relative URLs inside the bundled CSS resolve against the published
 * location because every reference stays inside the tree.
 */

// In the console environment, some path aliases may not exist. Please define these:
Yii::setAlias('@webroot', __DIR__ . '/../../../');
Yii::setAlias('@web', '/');

$bundles = ArrayHelper::merge(
    AppAsset::STATIC_DEPENDS,
    CoreBundleAsset::STATIC_DEPENDS,
);

$publishOptions = [
    'except' => [
        'scss/',
        '.gitignore',
    ],
];

// `basePath` is the filesystem location Yii writes the compressed output to
// and uses to compute relative URLs inside the bundled CSS. `baseUrl` would
// be the matching public URL — but the build never emits markup, Yii's CSS
// URL rewriting operates purely on filesystem paths, and our `saveTargets()`
// override strips it from `assets-prod.php` anyway. We pass an empty string
// where Yii requires the property to be set, so the build never suggests
// `protected/` is web-accessible.
return [
    // Adjust command/callback for JavaScript files compressing:
    'jsCompressor' => 'grunt uglify:assets  --from={from} --to={to} -d',
    // Adjust command/callback for CSS files compressing:
    'cssCompressor' => 'grunt cssmin --from={from} --to={to}',
    // The list of asset bundles to compress:
    'bundles' => $bundles,
    // Asset bundle for compression output:
    'targets' => [
        AppAsset::BUNDLE_NAME => [
            'class' => AssetBundle::class,
            'defer' => false,
            'defaultDepends' => false,
            'sourcePath' => '@humhub/resources',
            'basePath' => '@humhub/resources',
            'baseUrl' => '',
            'publishOptions' => $publishOptions,
            'jsPosition' => View::POS_HEAD,
            'js' => 'js/humhub-app.js',
            'css' => 'css/humhub-app.css',
            'preload' => [
                'js/humhub-app.js',
                'css/humhub-app.css',
            ],
            'depends' => AppAsset::STATIC_DEPENDS,
        ],
        CoreBundleAsset::BUNDLE_NAME => [
            'class' => AssetBundle::class,
            'defer' => true,
            'jsPosition' => View::POS_HEAD,
            'defaultDepends' => false,
            'sourcePath' => '@humhub/resources',
            'basePath' => '@humhub/resources',
            'baseUrl' => '',
            'publishOptions' => $publishOptions,
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
        'class' => \humhub\components\assets\BuildAssetManager::class,
        'basePath' => '@humhub/resources/build',
        'baseUrl' => '',
        'bundles' => [
            JqueryAsset::class => [
                'sourcePath' => '@npm/jquery/dist',
            ],
            JuiAsset::class => [
                'sourcePath' => '@npm/jquery-ui/dist',
            ],
            BootstrapAsset::class => [
                'sourcePath' => '@vendor/twbs/bootstrap/dist',
                'css' => [],
            ],
            BootstrapPluginAsset::class => [
                'sourcePath' => '@vendor/twbs/bootstrap/dist',
                'js' => ['js/bootstrap.bundle.min.js'],
                'depends' => [
                    JqueryAsset::class,
                    BootstrapAsset::class,
                    JuiBootstrapBridgeAsset::class,
                ],
            ],
            DatePickerLanguageAsset::class => [
                'sourcePath' => '@npm/jquery-ui',
            ],
            \yii\bootstrap\BootstrapAsset::class => [
                'sourcePath' => '@vendor/twbs/bootstrap/dist',
            ],
        ],
    ],
];
