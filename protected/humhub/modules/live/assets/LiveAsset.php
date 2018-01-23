<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\assets;

use yii\web\AssetBundle;

class LiveAsset extends AssetBundle
{
    public $sourcePath = '@live/resources';
    public $css = [];
    public $js = [
        'js/humhub.live.js',
        'js/humhub.live.poll.js'
    ];
}
