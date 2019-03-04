<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\assets;

use humhub\assets\CoreApiAsset;
use humhub\modules\stream\assets\StreamAsset;
use yii\web\AssetBundle;

class ActivityAsset extends AssetBundle
{

    public $sourcePath = '@activity/resources';

    public $css = [];

    public $js = [
        'js/humhub.activity.js'
    ];

    public $depends = [
        CoreApiAsset::class,
        StreamAsset::class
    ];

}
