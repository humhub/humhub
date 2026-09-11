<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\assets;

use humhub\components\assets\VueAssetBundle;
use humhub\modules\file\assets\FileVueAsset;
use humhub\modules\like\assets\LikeVueAsset;
use humhub\modules\user\assets\UserVueAsset;

/**
 * Compiled Vue components of the comment module (`vue/`, built via
 * `grunt build-vue --module=comment`).
 *
 * @since 1.20
 */
class CommentVueAsset extends VueAssetBundle
{
    /**
     * @inheritdoc
     */
    public string $moduleId = 'comment';

    /**
     * @inheritdoc
     *
     * The components `CommentEntry.vue` nests by tag: `<LikeButton>`, `<UserImage>` and `<AttachedFiles>`.
     */
    public $depends = [
        FileVueAsset::class,
        LikeVueAsset::class,
        UserVueAsset::class,
    ];
}
