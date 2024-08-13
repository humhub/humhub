<?php

namespace humhub\modules\content\assets;

use yii\web\AssetBundle;

class ContainerHeaderAsset extends AssetBundle
{
    public $sourcePath = '@content/resources';

    public $js = [
        'js/humhub.content.container.Header.js'
    ];
}
