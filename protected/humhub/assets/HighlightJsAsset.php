<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\assets;

use humhub\components\assets\WebResourcesAssetBundle;

class HighlightJsAsset extends WebResourcesAssetBundle
{
    /**
     * @inheritdoc
     */
    public $js = ['js/highlight.js/highlight.pack.js'];

    /**
     * @inheritdoc
     */
    public $depends = [
        HighlightJsStyleAsset::class,
    ];
}
