<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * AppDynamicAsset provides assets which are included in the core layout.
 * It similar to AppAsset but won't be compressed and combined.
 * So it can handle dynamic assets (e.g. javascript locales)
 *
 * @since 1.2
 */
class AppDynamicAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $jsOptions = ['position' => \yii\web\View::POS_HEAD];

    /**
     * @inheritdoc
     */
    public $depends = [
        'humhub\assets\JqueryTimeAgoLocaleAsset',
    ];

}
