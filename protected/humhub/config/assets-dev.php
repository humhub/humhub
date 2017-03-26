<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

return [
    'yii\bootstrap\BootstrapPluginAsset' => [
        'depends' => [
            'yii\web\JqueryAsset',
            'yii\bootstrap\BootstrapAsset',
            'humhub\assets\JuiBootstrapBridgeAsset'
        ]
    ]
];