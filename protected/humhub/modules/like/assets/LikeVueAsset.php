<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\assets;

use humhub\assets\CoreApiAsset;
use humhub\assets\CoreVueAsset;
use humhub\components\assets\AssetBundle;
use humhub\modules\user\assets\UserVueAsset;

/**
 * Compiled Vue components of the like module.
 *
 * Source: `vue/`, built via `grunt build-vue --module=like`.
 * The artifact is committed — see docs/develop/ui-js-vuejs.md.
 *
 * @since 1.20
 */
class LikeVueAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@like/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.like.vue.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        CoreApiAsset::class,
        // LikeButton.vue's user-list modal references <UiModal> by tag only - UiModal
        // lives in the core component set (protected/humhub/vue/) - CoreVueAsset must
        // register it before this bundle's own script runs, same reasoning
        // CommentVueAsset already documents for its own core/module component
        // dependencies.
        CoreVueAsset::class,
        // Same reasoning for <UserList> (renders the actual liker rows) - it lives in
        // the user module.
        UserVueAsset::class,
    ];
}
