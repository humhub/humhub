<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * Font Awesome is no longer shipped. Registering this bundle loads the Tabler Icons bundles
 * including the Font Awesome 4 compatibility layer instead, so markup with `fa-<name>` classes keeps rendering.
 *
 * @deprecated since 1.20, register [[IconAsset]] instead — or nothing, [[AppAsset]] already does. Will be removed in 1.21.
 */
class FontAwesomeAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $depends = [
        IconAsset::class,
    ];
}
