<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\assets;

use humhub\components\assets\AssetBundle;

/**
 * Compiled Vue components of the like module.
 *
 * Source: `resources/vue/`, built via `grunt build-vue --module=like`.
 * The artifact is committed — see docs/develop/ui-js-vuejs.md.
 *
 * @since 1.19
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
}
