<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Directory related assets.
 * 
 * @author buddha
 */
class DirectoryAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $sourcePath = '@directory/resources';
    
    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.directory.js'
    ];
    
    /**
     * @inheritdoc
     */
    public $jsOptions = ['position' => View::POS_END];
    
    /**
     * @inheritdoc
     */
    public $depends = [
        'humhub\assets\JqueryKnobAsset'
    ];

}
