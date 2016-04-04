<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/animate.min.css',
        'css/temp.css',
        'css/bootstrap-wysihtml5.css',
        'css/flatelements.css',
        'resources/at/jquery.atwho.css',
    ];
    public $jsOptions = ['position' => \yii\web\View::POS_BEGIN];
    public $js = [
        'js/ekko-lightbox-modified.js',
        'js/modernizr.js',
        'js/jquery.cookie.js',
        'js/jquery.highlight.min.js',
        'js/jquery.autosize.min.js',
        'js/wysihtml5-0.3.0.js',
        'js/bootstrap3-wysihtml5.js',
        'js/jquery.color-2.1.0.min.js',
        'js/jquery.flatelements.js',
        'js/jquery.loader.js',
        'js/desktop-notify-min.js',
        'js/desktop-notify-config.js',
        'resources/at/jquery.caret.min.js',
        'resources/at/jquery.atwho.min.js',
        'resources/file/fileuploader.js',
        'resources/user/userpicker.js',
        'js/jquery.nicescroll.min.js',
        'js/app.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        /**
         * Temporary disabled
         * https://github.com/inuyaksa/jquery.nicescroll/issues/574
         */
        //'humhub\assets\JqueryNiceScrollAsset', 
        'humhub\assets\JqueryTimeAgoAsset',
        'humhub\assets\JqueryKnobAsset',
        'humhub\assets\JqueryWidgetAsset',
        'humhub\assets\JqueryPlaceholderAsset',
        'humhub\assets\FontAwesomeAsset',
        'humhub\assets\BlueimpFileUploadAsset',
    ];

}
