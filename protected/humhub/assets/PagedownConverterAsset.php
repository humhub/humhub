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
 * @deprecated since v1.3
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
        'js/pagedown/Markdown.Converter.js',
        'js/pagedown/Markdown.Sanitizer.js',
        'js/pagedown/Markdown.Extra.js',
    ];

}
