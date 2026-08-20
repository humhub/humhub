<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\assets;

use humhub\assets\CoreApiAsset;
use humhub\components\assets\AssetBundle;

/**
 * Compiled Vue components of the user module.
 *
 * Source: `vue/`, built via `grunt build-vue --module=user`.
 * The artifact is committed — see docs/develop/ui-js-vuejs.md.
 *
 * @since 1.19
 */
class UserVueAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@user/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.user.vue.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        CoreApiAsset::class,
    ];
}
