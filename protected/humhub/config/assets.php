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
use yii\helpers\FileHelper;
use yii\jui\DatePickerLanguageAsset;
use yii\jui\JuiAsset;
use yii\web\JqueryAsset;

/**
 * Configuration file for the "yii asset" console command.
 */

// In the console environment, some path aliases may not exist. Please define these:
Yii::setAlias('@webroot', __DIR__ . '/../../../');
Yii::setAlias('@web', '/');

$bundlesPath = Yii::getAlias('@webroot/assets/bundles');
if (!is_dir($bundlesPath)) {
    FileHelper::createDirectory($bundlesPath . '/js');
    FileHelper::createDirectory($bundlesPath . '/css');
}

$bundles = ArrayHelper::merge(
    [AppAsset::class, CoreBundleAsset::class],
    AppAsset::STATIC_DEPENDS,
    CoreBundleAsset::STATIC_DEPENDS,
);

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
            'basePath' => '@webroot/assets/bundles',
            'baseUrl' => '@web/assets/bundles',
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
            'basePath' => '@webroot/assets/bundles',
            'baseUrl' => '@web/assets/bundles',
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
        'basePath' => Yii::$app->assetManager->basePath,
        'baseUrl' => Yii::$app->assetManager->baseUrl,
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
