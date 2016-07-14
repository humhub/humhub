<?php

namespace xj\jplayer;

use Yii;
use yii\web\AssetBundle;

class CircleAssets extends AssetBundle
{

    public $sourcePath = '@vendor/xj/yii2-jplayer-widget/assets';
    public $basePath = '@webroot/assets';
    public $css = [];
    public $js = [
        'js/jquery.transform2d.js',
        'js/jquery.grab.js',
        'js/mod.csstransforms.min.js',
        'js/circle.player.js',
    ];
    public $depends = ['xj\jplayer\CommonAssets'];

}
