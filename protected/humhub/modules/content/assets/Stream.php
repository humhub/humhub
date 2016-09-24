<?php

namespace humhub\modules\content\assets;

use yii\web\AssetBundle;

class Stream extends AssetBundle
{

    public $sourcePath = '@humhub/modules/content/assets/resources';
    public $css = [
    ];
    public $js = [
        'stream.js',
        'wall.js',
        'utils.js'
    ];

}
