<?php


namespace humhub\modules\ui\form\assets;


use humhub\assets\BootstrapMarkdownAsset;
use humhub\components\assets\AssetBundle;

class MarkdownFieldAsset extends AssetBundle
{
    public $sourcePath = '@ui/form/resources';

    public $css = [
        'css/bootstrap-markdown-override.css'
    ];

    public $js = [
        'js/humhub.ui.markdown.js'
    ];

    public $depends = [
        BootstrapMarkdownAsset::class
    ];
}
