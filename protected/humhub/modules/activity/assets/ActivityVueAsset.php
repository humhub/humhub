<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\assets;

use humhub\assets\CoreApiAsset;
use humhub\assets\CoreVueAsset;
use humhub\components\assets\AssetBundle;
use humhub\modules\space\assets\SpaceVueAsset;
use humhub\modules\user\assets\UserVueAsset;

/**
 * Compiled Vue components of the activity module.
 *
 * Source: `vue/`, built via `grunt build-vue --module=activity`.
 * The artifact is committed — see docs/develop/ui-js-vuejs.md.
 *
 * @since 1.20
 */
class ActivityVueAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@activity/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.activity.vue.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        CoreApiAsset::class,
        // The island renders the two module-provided shared components an entry needs:
        // `<UserImage>` for the author and `<SpaceImage>` for the space badge. Both must be
        // registered before this bundle's own script runs.
        CoreVueAsset::class,
        UserVueAsset::class,
        SpaceVueAsset::class,
    ];
}
