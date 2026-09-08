<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\assets;

use humhub\components\assets\VueAssetBundle;
use humhub\modules\space\assets\SpaceVueAsset;
use humhub\modules\user\assets\UserVueAsset;

/**
 * Compiled Vue components of the notification module (`vue/`, built via
 * `grunt build-vue --module=notification`).
 *
 * @since 1.20
 */
class NotificationVueAsset extends VueAssetBundle
{
    /**
     * @inheritdoc
     */
    public string $moduleId = 'notification';

    /**
     * @inheritdoc
     *
     * The components an entry nests by tag: `<UserImage>` for the originator, `<SpaceImage>` for the space badge.
     */
    public $depends = [
        SpaceVueAsset::class,
        UserVueAsset::class,
    ];
}
