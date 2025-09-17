<?php

namespace humhub\modules\file\assets;

use humhub\components\assets\AssetBundle;

class ActiveFileUploadAsset extends AssetBundle
{
    public $sourcePath = '@file/resources';

    public $js = [
        'js/humhub.ActiveFileUpload.js',
    ];

    public $css = [
        'css/humhub.ActiveFileUpload.css',
    ];
}
