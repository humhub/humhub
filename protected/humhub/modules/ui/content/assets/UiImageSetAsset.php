<?php

namespace humhub\modules\ui\content\assets;


use humhub\components\assets\CoreAssetBundle;

class UiImageSetAsset extends CoreAssetBundle
{
    public $sourcePath = '@ui/content/resources';

    public $js = [
        'js/humhub.ui.imageset.js',
    ];

    public $css = [
        'css/humhub.ui.imageset.css',
    ];
}
