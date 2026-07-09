<?php

namespace humhub\assets;

use humhub\components\assets\AssetBundle;
use humhub\components\View;

class IntlMessageFormatAsset extends AssetBundle
{
    public $defaultDepends = false;
    public $defer = false;
    public $jsPosition = View::POS_HEAD;
    public $sourcePath = '@npm/intl-messageformat';
    public $publishOptions = [
        'only' => [
            'intl-messageformat.iife.js',
        ],
    ];
    public $js = [
        'intl-messageformat.iife.js',
    ];
}
