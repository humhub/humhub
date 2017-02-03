<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * The AppAsset assets are included in the core layout.
 * This Assetbundle includes some core dependencies and the humhub core api.
 */
class AppAsset extends AssetBundle
{

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
    public $css = [
        'css/temp.css',
        'css/bootstrap-wysihtml5.css',
        'css/flatelements.css',
        'css/blueimp-gallery.min.css'
    ];

    /**
     * @inheritdoc
     */
    public $jsOptions = ['position' => \yii\web\View::POS_HEAD];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/blueimp-gallery.min.js',
        'js/jquery.highlight.min.js',
        'js/desktop-notify-min.js',
        'js/desktop-notify-config.js',
        'js/jquery.nicescroll.min.js',
        'resources/file/fileuploader.js',
        'resources/user/userpicker.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'humhub\assets\BluebirdAsset',
        'humhub\assets\JqueryTimeAgoAsset',
        'humhub\assets\JqueryWidgetAsset',
        'humhub\assets\JqueryColorAsset',
        'humhub\assets\JqueryPlaceholderAsset',
        'humhub\assets\FontAwesomeAsset',
        'humhub\assets\BlueimpFileUploadAsset',
        'humhub\assets\JqueryHighlightAsset',
        'humhub\assets\JqueryCookieAsset',
        'humhub\assets\JqueryAutosizeAsset',
        'humhub\assets\AtJsAsset',
        'humhub\assets\AnimateCssAsset',
        'humhub\assets\CoreApiAsset',
        'humhub\modules\live\assets\LiveAsset',
        'humhub\modules\notification\assets\NotificationAsset',
        'humhub\modules\content\assets\ContentAsset',
        'humhub\modules\user\assets\UserAsset',
        'humhub\modules\user\assets\UserPickerAsset',
        'humhub\modules\file\assets\FileAsset',
        'humhub\modules\post\assets\PostAsset',
        'humhub\modules\space\assets\SpaceAsset',
        'humhub\modules\comment\assets\CommentAsset',
        'humhub\assets\NProgressAsset',
        'humhub\assets\IE9FixesAsset',
        'humhub\assets\IEFixesAsset',
        'humhub\assets\PagedownConverterAsset',
    ];

}
