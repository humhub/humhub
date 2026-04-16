<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\assets;

use humhub\components\assets\WebResourcesAssetBundle;

class HighlightJsStyleAsset extends WebResourcesAssetBundle
{
    /**
     * @inheritdoc
     */
    public $css = ['js/highlight.js/styles/github.css'];
}
