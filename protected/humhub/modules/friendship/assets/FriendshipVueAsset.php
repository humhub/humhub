<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\assets;

use humhub\assets\CoreApiAsset;
use humhub\components\assets\AssetBundle;

/**
 * Compiled Vue components of the friendship module.
 *
 * Source: `vue/`, built via `grunt build-vue --module=friendship`.
 * The artifact is committed — see docs/develop/ui-js-vuejs.md.
 *
 * @since 1.20
 */
class FriendshipVueAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@friendship/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.friendship.vue.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        CoreApiAsset::class,
    ];
}
