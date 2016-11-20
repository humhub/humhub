<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\assets;

use yii\web\AssetBundle;

class LikeAsset extends AssetBundle
{

    public $jsOptions = ['position' => \yii\web\View::POS_END];
    public $sourcePath = '@like/assets';
    public $css = [];
    public $js = [
        'js/humhub.like.js'
    ];
}
