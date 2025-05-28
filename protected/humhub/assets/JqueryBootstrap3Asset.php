<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\WebStaticAssetBundle;

/**
 * Compatibility with Bootstrap 3
 *
 * $deprecated since 1.18
 *
 * TODO: Remove when Bootstrap 3 is no longer supported
 */
class JqueryBootstrap3Asset extends WebStaticAssetBundle
{
    /**
     * @inheritdoc
     */
    public $js = ['js/jquery.bootstrap3.js'];
}
