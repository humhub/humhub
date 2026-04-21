<?php

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

class GridViewAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@humhub/resources';

    public $js = [
        'js/grid-view.js',
    ];

    public $css = [
        'css/grid-view.css',
    ];
}
