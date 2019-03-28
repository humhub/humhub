<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * AppAsset includes HumHub core assets to the main layout.
 * This Assetbundle includes some core dependencies and the humhub core api.
 *
 * Note: All CSS/JS files will be compressed and bundled. If you need dynamic
 * css/js loading e.g. based on users locale: see AppDynamicAsset
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
        'humhub\modules\content\assets\ProseMirrorRichTextAsset',
        'humhub\modules\user\assets\UserAsset',
        'humhub\modules\live\assets\LiveAsset',
        'humhub\modules\notification\assets\NotificationAsset',
        'humhub\modules\content\assets\ContentAsset',
        'humhub\modules\content\assets\ContentContainerAsset',
        'humhub\modules\user\assets\UserPickerAsset',
        'humhub\modules\file\assets\FileAsset',
        'humhub\modules\post\assets\PostAsset',
        'humhub\modules\space\assets\SpaceAsset',
        'humhub\modules\topic\assets\TopicAsset',
        'humhub\modules\ui\filter\assets\FilterAsset',
        'humhub\modules\comment\assets\CommentAsset',
        'humhub\modules\like\assets\LikeAsset',
        'humhub\assets\NProgressAsset',
        'humhub\assets\IE9FixesAsset',
        'humhub\assets\IEFixesAsset',
        'humhub\assets\PagedownConverterAsset',
        'humhub\assets\ClipboardJsAsset',
        'humhub\assets\ImagesLoadedAsset',
        'humhub\assets\SocketIoAsset',
        'humhub\assets\OpenSansAsset',
        'humhub\assets\HighlightJsAsset',
    ];

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
    public static function register($view)
    {
        $instance = parent::register($view);
        $view->registerAssetBundle(AppDynamicAsset::class);

        return $instance;
    }

}
