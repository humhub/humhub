<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\assets;


use yii\web\AssetBundle;

class HighlightJsAsset extends AssetBundle
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
    public $js = ['js/highlight.js/highlight.pack.js'];

    /**
     * @inheritdoc
     */
    public $css = ['js/highlight.js/styles/github.css'];
}
