<?php


namespace humhub\modules\ui\form\assets;


use humhub\assets\BootstrapMarkdownAsset;
use humhub\components\assets\AssetBundle;

/**
 * Class MarkdownFieldAsset
 * @package humhub\modules\ui\form\assets
 * @deprecated since 1.5 Use `humhub\modules\content\widgets\richtext\RichTextField`
 */
class MarkdownFieldAsset extends AssetBundle
{
    public $sourcePath = '@ui/form/resources';

    public $css = [
        'css/bootstrap-markdown-override.css'
    ];

    public $js = [
        'js/markdownEditor.js',
        'js/humhub.ui.markdown.js'
    ];

    public $depends = [
        BootstrapMarkdownAsset::class
    ];
}
