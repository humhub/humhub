<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\assets;

use humhub\components\assets\VueAssetBundle;

/**
 * Compiled Vue components of the file module (`vue/`, built via
 * `grunt build-vue --module=file`).
 *
 * @since 1.20
 */
class FileVueAsset extends VueAssetBundle
{
    /**
     * @inheritdoc
     */
    public string $moduleId = 'file';
}
