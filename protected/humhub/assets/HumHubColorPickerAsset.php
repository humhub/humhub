<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * Color Picker js utility
 *
 * @author buddha
 */
class HumHubColorPickerAsset extends AssetBundle
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
    public $js = ['js/humhub/humhub.ui.colorpicker.js'];


    public $depends = ['humhub\assets\BootstrapColorPickerAsset'];

}
