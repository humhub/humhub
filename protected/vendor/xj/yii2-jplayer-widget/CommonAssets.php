<?php

namespace xj\jplayer;

use Yii;
use yii\web\AssetBundle;

class CommonAssets extends AssetBundle
{
    public $sourcePath = '@vendor/xj/yii2-jplayer-widget/assets';
    public $basePath = '@webroot/assets';
    public $js = [
        'js/jquery.jplayer.min.js',
        'js/jquery.jplayer.inspector.js',
    ];
    public $css = [];
    public $depends = ['yii\web\JqueryAsset'];

}
