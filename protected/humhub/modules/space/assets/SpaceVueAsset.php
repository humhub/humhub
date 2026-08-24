<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\assets;

use humhub\assets\CoreApiAsset;
use humhub\components\assets\AssetBundle;

/**
 * Compiled Vue components of the space module.
 *
 * Source: `vue/`, built via `grunt build-vue --module=space`.
 * The artifact is committed — see docs/develop/ui-js-vuejs.md.
 *
 * @since 1.20
 */
class SpaceVueAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@space/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.space.vue.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        CoreApiAsset::class,
    ];
}
