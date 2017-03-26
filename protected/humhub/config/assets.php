<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * Configuration file for the "yii asset" console command.
 */

// In the console environment, some path aliases may not exist. Please define these:
Yii::setAlias('@webroot', __DIR__ . '/../../../');
Yii::setAlias('@web', '/');

Yii::setAlias('@webroot-static', __DIR__ . '/../../../static');
Yii::setAlias('@web-static', '/static');

return [
    // Adjust command/callback for JavaScript files compressing:
    'jsCompressor' => 'grunt uglify:assets  --from={from} --to={to} -d',
    // Adjust command/callback for CSS files compressing:
    'cssCompressor' => 'grunt cssmin --from={from} --to={to}',
    // The list of asset bundles to compress:
    'bundles' => [
        'humhub\assets\AppAsset',
    ],
    // Asset bundle for compression output:
    'targets' => [
        'all' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot-static',
            'baseUrl' => '@web-static',
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
        ],
    ],
    // Asset manager configuration:
    'assetManager' => [
        'basePath' => '@webroot-static/assets',
        'baseUrl' => '@web-static/assets',
        'bundles' => [
            'yii\bootstrap\BootstrapPluginAsset' => [
                'js' => ['js/bootstrap.min.js'],
                'depends' => [
                    'yii\web\JqueryAsset',
                    'yii\bootstrap\BootstrapAsset',
                    'humhub\assets\JuiBootstrapBridgeAsset'
                ]
            ],
        ]
    ],
];