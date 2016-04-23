<?php

namespace humhub\modules\stream\assets;

use yii\web\AssetBundle;

class Stream extends AssetBundle
{

    public $sourcePath = '@humhub/modules/stream/resources';
    public $css = [
    ];
    public $js = [
        'stream.js',
        'wall.js',
        'utils.js'
    ];

}
