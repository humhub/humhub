<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\assets;

use humhub\components\assets\VueAssetBundle;

/**
 * Compiled Vue components of the friendship module (`vue/`, built via
 * `grunt build-vue --module=friendship`).
 *
 * @since 1.20
 */
class FriendshipVueAsset extends VueAssetBundle
{
    /**
     * @inheritdoc
     */
    public string $moduleId = 'friendship';
}
