<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\assets;

use humhub\components\assets\VueAssetBundle;

/**
 * Compiled Vue components of the space module (`vue/`, built via
 * `grunt build-vue --module=space`).
 *
 * @since 1.20
 */
class SpaceVueAsset extends VueAssetBundle
{
    /**
     * @inheritdoc
     */
    public string $moduleId = 'space';
}
