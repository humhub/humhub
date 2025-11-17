<?php

namespace humhub\libs\Utf8Trim;

use yii\validators\ValidationAsset;
use yii\web\AssetBundle;

class Utf8TrimAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/dist';

    public $js = [
        'js/ExtraTrimFilterAsset.js'
    ];

    public $depends = [
        ValidationAsset::class,
    ];
}
