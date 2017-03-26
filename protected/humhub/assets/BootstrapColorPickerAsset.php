<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-color
 *
 * @author buddha
 */
class BootstrapColorPickerAsset extends AssetBundle
{

    public $jsOptions = ['position' => \yii\web\View::POS_BEGIN];

    /**
     * @inheritdoc
     */
    public $basePath = '@webroot-static';

    /**
     * @inheritdoc
     */
    public $baseUrl = '@web-static';

    /**
     * @inheritdoc
     */
    public $js = ['js/colorpicker/js/bootstrap-colorpicker-modified.js'];

     /**
     * @inheritdoc
     */
    public $css = ['js/colorpicker/css/bootstrap-colorpicker.min.css'];

}
