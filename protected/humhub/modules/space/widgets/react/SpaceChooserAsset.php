<?php

namespace humhub\modules\space\widgets\react;

use yii\web\AssetBundle;

class SpaceChooserAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/assets';

    public $js = [
        'entry.vue.js'
    ];

    public $publishOptions = [
        'forceCopy' => YII_DEBUG
    ];
}
