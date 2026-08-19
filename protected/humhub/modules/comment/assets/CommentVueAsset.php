<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\assets;

use humhub\assets\CoreApiAsset;
use humhub\assets\CoreVueAsset;
use humhub\components\assets\AssetBundle;
use humhub\modules\like\assets\LikeVueAsset;

/**
 * Compiled Vue components of the comment module.
 *
 * Source: `vue/`, built via `grunt build-vue --module=comment`.
 * The artifact is committed — see docs/develop/ui-js-vuejs.md.
 *
 * @since 1.19
 */
class CommentVueAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@comment/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.comment.vue.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        CoreApiAsset::class,
        // CommentEntry.vue/CommentForm.vue reference <RichTextOutput>/<LegacyFormWrapper> by
        // tag only (they moved to core - see docs/develop/ui-js-vuejs.md) - CoreVueAsset must
        // register them before this bundle's own script runs, or the first render warns
        // "Failed to resolve component" and silently skips them.
        CoreVueAsset::class,
        // CommentEntry.vue references <LikeButton> by tag only - LikeVueAsset must register
        // it before this bundle's own script runs, or the first render warns "Failed to
        // resolve component" and silently skips the like button (see the P2-4 review note in
        // docs/superpowers/plans/2026-08-19-vuejs-comments.md).
        LikeVueAsset::class,
    ];
}
