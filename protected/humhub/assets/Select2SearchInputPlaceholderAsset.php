<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\WebResourcesAssetBundle;

/**
 * Search Input Placeholder plugin for Select2
 */
class Select2SearchInputPlaceholderAsset extends WebResourcesAssetBundle
{
    /**
     * @inheritdoc
     */
    public $js = ['js/select2-searchInputPlaceholder.min.js'];
}
