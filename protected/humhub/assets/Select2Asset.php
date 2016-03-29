<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-knob
 * 
 * @author luke
 */
class Select2Asset extends AssetBundle
{

    public $jsOptions = ['position' => \yii\web\View::POS_BEGIN];
    
    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/select2';

    /**
     * @inheritdoc
     */
    public $js = ['dist/js/select2.min.js'];
    
    /**
     * @inheritdoc
     */
    public $css = ['dist/css/select2.min.css'];
    
    public $depends = [
        'humhub\assets\AppAsset'
    ];

}
