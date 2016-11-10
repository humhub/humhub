<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-color
 * 
 * @author buddha
 */
class JqueryColorPickerAsset extends AssetBundle
{

    public $jsOptions = ['position' => \yii\web\View::POS_BEGIN];
    
    /**
     * @inheritdoc
     */
    public $sourcePath = '@webroot/resources/space/colorpicker';

    /**
     * @inheritdoc
     */
    public $js = ['js/bootstrap-colorpicker-modified.js'];
    
    /**
     * @inheritdoc
     */
    public $css = ['css/bootstrap-colorpicker.min.css'];
}
