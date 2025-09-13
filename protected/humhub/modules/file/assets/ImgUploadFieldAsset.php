<?php

namespace humhub\modules\file\assets;

use humhub\components\assets\AssetBundle;

class ImgUploadFieldAsset extends AssetBundle
{
    public $sourcePath = '@file/resources';

    public $js = [
        'js/humhub.imgUploadField.js',
    ];

    public $css = [
        'css/humhub.imgUploadField.css',
    ];
}
