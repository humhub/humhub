<?php

namespace humhub\modules\content\assets;

use humhub\components\assets\CoreAssetBundle;

class ContainerHeaderAsset extends CoreAssetBundle
{
    public $sourcePath = '@content/resources';

    public $js = [
        'js/humhub.content.container.Header.js',
    ];
}
