<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\assets;

use humhub\assets\CoreApiAsset;
use humhub\assets\CoreVueAsset;
use humhub\components\assets\AssetBundle;
use humhub\modules\space\assets\SpaceVueAsset;
use humhub\modules\user\assets\UserVueAsset;

/**
 * Compiled Vue components of the notification module.
 *
 * Source: `vue/`, built via `grunt build-vue --module=notification`.
 * The artifact is committed — see docs/develop/ui-js-vuejs.md.
 *
 * @since 1.20
 */
class NotificationVueAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@notification/resources';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/humhub.notification.vue.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        CoreApiAsset::class,
        // The islands nest components from the core set (none by tag today, but the shared
        // runtime lives there) and the two module-provided shared components an entry renders:
        // `<UserImage>` for the originator and `<SpaceImage>` for the space badge. Both must be
        // registered before this bundle's own script runs.
        CoreVueAsset::class,
        UserVueAsset::class,
        SpaceVueAsset::class,
    ];
}
