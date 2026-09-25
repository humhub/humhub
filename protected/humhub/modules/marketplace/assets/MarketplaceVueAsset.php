<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\marketplace\assets;

use humhub\components\assets\VueAssetBundle;

/**
 * The marketplace's Vue islands (`js/humhub.marketplace.vue.js`). Empty on purpose: the class
 * is the bundle's identity for `depends` and `VueWidget::$assetBundle`.
 *
 * @since 1.20
 */
class MarketplaceVueAsset extends VueAssetBundle
{
}
