<?php

namespace humhub\core\content\assets;

use yii\web\AssetBundle;

class Stream extends AssetBundle
{

    public $sourcePath = '@humhub/core/content/assets/resources';
    public $css = [
    ];
    public $js = [
        'stream.js',
        'wall.js',
        'utils.js'
    ];

}
