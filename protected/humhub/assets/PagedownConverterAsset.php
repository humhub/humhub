<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use yii\web\AssetBundle;

/**
 * jquery-highlight
 *
 * @author buddha
 */
class PagedownConverterAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public $basePath = '@webroot-static';

    /**
     * @inheritdoc
     */
    public $baseUrl = '@web-static';

    /**
     * @inheritdoc
     */
    public $js = [
        'resources/js/pagedown/Markdown.Converter.js',
        'resources/js/pagedown/Markdown.Sanitizer.js',
        'resources/js/pagedown/Markdown.Extra.js',
    ];

}
