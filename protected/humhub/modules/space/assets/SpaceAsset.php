<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\assets;

use yii\web\AssetBundle;

class SpaceAsset extends AssetBundle
{
    public $sourcePath = '@space/resources';
    public $css = [];
    public $js = [
        'js/humhub.space.js'
    ];
}
