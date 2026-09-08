<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\assets;

use humhub\components\assets\VueAssetBundle;
use humhub\modules\user\assets\UserVueAsset;

/**
 * Compiled Vue components of the like module (`vue/`, built via
 * `grunt build-vue --module=like`).
 *
 * @since 1.20
 */
class LikeVueAsset extends VueAssetBundle
{
    /**
     * @inheritdoc
     */
    public string $moduleId = 'like';

    /**
     * @inheritdoc
     *
     * The user-list modal of `LikeButton.vue` nests `<UserList>` by tag.
     */
    public $depends = [
        UserVueAsset::class,
    ];
}
