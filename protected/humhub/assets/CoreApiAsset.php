<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * HumHub Core Api Asset
 */
class CoreApiAsset extends AssetBundle
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
    public $css = [];

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
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub/legacy/jquery.loader.js',
        'js/humhub/legacy/app.js',
        'js/humhub/humhub.core.js',
        'js/humhub/humhub.util.js',
        'js/humhub/humhub.log.js',
        'js/humhub/humhub.ui.view.js',
        'js/humhub/humhub.ui.additions.js',
        'js/humhub/humhub.ui.showMore.js',
        'js/humhub/humhub.ui.form.elements.js',
        'js/humhub/humhub.ui.loader.js',
        'js/humhub/humhub.action.js',
        'js/humhub/humhub.ui.widget.js',
        'js/humhub/humhub.ui.modal.js',
        'js/humhub/humhub.ui.progress.js',
        'js/humhub/humhub.client.js',
        'js/humhub/humhub.ui.status.js',
        'js/humhub/humhub.ui.navigation.js',
        'js/humhub/humhub.ui.gallery.js',
        'js/humhub/humhub.ui.picker.js',
        'js/humhub/humhub.ui.richtext.js',
        'js/humhub/humhub.ui.markdown.js',
        'js/humhub/humhub.media.Jplayer.js',
        // Note this should stay at last for other click event listeners beeing able to prevent pjax handling (e.g gallery)
        'js/humhub/humhub.client.pjax.js',
    ];

}
