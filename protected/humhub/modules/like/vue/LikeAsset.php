<?php

namespace humhub\modules\like\vue;

use yii\web\AssetBundle;

class LikeAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/assets';

    public $js = [
        'entry.vue.js'
    ];

    public $publishOptions = [
//        'forceCopy' => YII_DEBUG
    ];
}
