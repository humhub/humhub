<?php


namespace humhub\assets;

use humhub\components\assets\WebStaticAssetBundle;

class CoreExtensionAsset extends WebStaticAssetBundle
{
    /**
     * @inheritdoc
     */
    public $defaultDepends = false;

    /**
     * @inheritdoc
     */
    public $js = [

        'js/humhub/humhub.ui.form.elements.js',
        'js/humhub/humhub.ui.form.js',
        'js/humhub/humhub.ui.showMore.js',
        'js/humhub/humhub.ui.panel.js',
        'js/humhub/humhub.ui.gallery.js',
        'js/humhub/humhub.ui.picker.js',
        'js/humhub/humhub.ui.codemirror.js',
        'js/humhub/humhub.oembed.js',
        'js/humhub/humhub.media.Jplayer.js',
        // Note this should stay at last for other click event listeners beeing able to prevent pjax handling (e.g gallery)
        'js/humhub/humhub.client.pjax.js',
    ];
}
