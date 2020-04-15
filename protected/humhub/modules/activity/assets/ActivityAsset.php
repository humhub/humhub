<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\assets;

use humhub\components\assets\AssetBundle;
use humhub\modules\stream\assets\StreamAsset;

class ActivityAsset extends AssetBundle
{

    public $sourcePath = '@activity/resources';

    public $js = [
        'js/humhub.activity.js'
    ];

    public $depends = [
        StreamAsset::class
    ];

}
