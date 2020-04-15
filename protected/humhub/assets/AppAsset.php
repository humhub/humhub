<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\WebStaticAssetBundle;
use humhub\modules\content\assets\ContentAsset;
use humhub\modules\file\assets\FileAsset;
use yii\bootstrap\BootstrapAsset;
use yii\bootstrap\BootstrapPluginAsset;
use yii\jui\JuiAsset;
use yii\validators\ValidationAsset;
use yii\web\JqueryAsset;
use yii\web\View;
use yii\web\YiiAsset;
use yii\widgets\ActiveFormAsset;

/**
 * AppAsset includes HumHub core assets to the main layout.
 * This Assetbundle includes some core dependencies and the humhub core api.
 *
 * Note: All CSS/JS files will be compressed and bundled. If you need dynamic
 * css/js loading e.g. based on users locale: see AppDynamicAsset
 */
class AppAsset extends WebStaticAssetBundle
{
    /**
     * @inheritdoc
     */
    public $defer = false;

    /**
     * @inheritdoc
     */
    public $defaultDepends = false;

    /**
     * @inheritdoc
     */
    public $jsPosition = View::POS_HEAD;

    const BUNDLE_NAME = 'app';

    const STATIC_DEPENDS = [
        JqueryAsset::class,
        JuiBootstrapBridgeAsset::class,
        JuiAsset::class,
        YiiAsset::class,
        ActiveFormAsset::class,
        ValidationAsset::class,
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
        BluebirdAsset::class,
        FontAwesomeAsset::class,
        AnimateCssAsset::class, // preload
        OpenSansAsset::class,
        PjaxAsset::class,
        JqueryTimeAgoAsset::class,

        /**
         * Style only assets
         */
        HighlightJsStyleAsset::class,
        NProgressStyleAsset::class,
        Select2StyleAsset::class,
        BlueimpGalleryStyleAsset::class,
        FlatelementsStyleAsset::class,


        /**
         * Polyfills
         */
        IntersectionObserverPolyfillAsset::class,



        /**
         * Core HumHub API + commonly required modules
         */
        CoreApiAsset::class,
        ContentAsset::class,
        FileAsset::class,
    ];

    /**
     * @inheritdoc
     */
    public $depends = self::STATIC_DEPENDS;

    /**
     * @inheritdoc
     */
    public $js = [
        'js/desktop-notify-min.js',
        'js/desktop-notify-config.js',
    ];

    /**
     * @inheritdoc
     */
    public static function register($view)
    {
        $instance = parent::register($view);

        AppDynamicAsset::register($view);
        CoreBundleAsset::register($view);

        return $instance;
    }

}
