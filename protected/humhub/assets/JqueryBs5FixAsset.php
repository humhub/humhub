<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\WebResourcesAssetBundle;

/**
 * jQuery Fix to work with Bootstrap 5
 *
 * $deprecated since 1.18
 *
 * TODO: Remove when jQuery is no longer supported
 */
class JqueryBs5FixAsset extends WebResourcesAssetBundle
{
    /**
     * @inheritdoc
     */
    public $js = ['js/jquery.bs5-fix.js'];
}
