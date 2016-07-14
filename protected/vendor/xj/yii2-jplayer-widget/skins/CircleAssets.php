<?php

namespace xj\jplayer\skins;

use Yii;
use yii\web\AssetBundle;

class CircleAssets extends AssetBundle {

    public $sourcePath = '@vendor/xj/yii2-jplayer-widget/assets';
    public $basePath = '@webroot/assets';
    public $css = ['skin/circle.skin/circle.player.css'];

}
