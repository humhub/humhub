<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\assets;

use humhub\components\assets\VueAssetBundle;

/**
 * Compiled Vue components of the content module (`vue/`, built via
 * `grunt build-vue --module=content`).
 *
 * @since 1.20
 */
class ContentVueAsset extends VueAssetBundle
{
    /**
     * @inheritdoc
     */
    public string $moduleId = 'content';
}
