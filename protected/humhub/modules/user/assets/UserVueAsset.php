<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\assets;

use humhub\components\assets\VueAssetBundle;

/**
 * Compiled Vue components of the user module (`vue/`, built via
 * `grunt build-vue --module=user`).
 *
 * @since 1.20
 */
class UserVueAsset extends VueAssetBundle
{
    /**
     * @inheritdoc
     */
    public string $moduleId = 'user';
}
