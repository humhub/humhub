<?php

namespace humhub\modules\ui\assets;

use yii\web\AssetBundle;

class UiImageSetAsset extends AssetBundle
{
    public $sourcePath = '@ui/resources';

    public $js = [
        'js/humhub.ui.imageset.js'
    ];

    public $css = [
        'css/humhub.ui.imageset.css'
    ];
}