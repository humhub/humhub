<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\assets;

use yii\web\AssetBundle;
use yii\web\View;

class DirectoryAsset extends AssetBundle
{

    public $sourcePath = '@directory/resources';
    public $css = [];
    public $js = [
        'js/humhub.directory.js'
    ];
    
    public $jsOptions = ['position' => View::POS_END];
    
    public $depends = [
        'humhub\assets\JqueryKnobAsset'
    ];

}
