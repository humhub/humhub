<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

return [
    'yii\bootstrap5\BootstrapPluginAsset' => [
        'depends' => [
            'yii\web\JqueryAsset',
            'yii\bootstrap5\BootstrapAsset',
            'humhub\assets\JuiBootstrapBridgeAsset'
        ]
    ],
    'yii\web\JqueryAsset' => [
        'sourcePath' => '@npm/jquery/dist',
    ],
    'yii\jui\JuiAsset' => [
        'sourcePath' => '@npm/jquery-ui/dist'
    ],
];
