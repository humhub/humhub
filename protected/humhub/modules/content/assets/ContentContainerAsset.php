<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\assets;

use yii\web\AssetBundle;

class ContentContainerAsset extends AssetBundle
{

    public $jsOptions = ['position' => \yii\web\View::POS_END];
    
    public $sourcePath = '@content/assets';
    public $css = [];
    public $js = [
        'js/humhub.content.container.js'
    ];

}
