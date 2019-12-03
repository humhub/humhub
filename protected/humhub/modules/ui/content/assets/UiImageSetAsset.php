<?php

namespace humhub\modules\ui\content\assets;

use yii\web\AssetBundle;

class UiImageSetAsset extends AssetBundle
{
    public $sourcePath = '@ui/content/resources';

    public $js = [
        'js/humhub.ui.imageset.js'
    ];

    public $css = [
        'css/humhub.ui.imageset.css'
    ];
}
