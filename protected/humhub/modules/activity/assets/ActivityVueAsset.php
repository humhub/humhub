<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\assets;

use humhub\components\assets\VueAssetBundle;
use humhub\modules\space\assets\SpaceVueAsset;
use humhub\modules\user\assets\UserVueAsset;

/**
 * Compiled Vue components of the activity module (`vue/`, built via
 * `grunt build-vue --module=activity`).
 *
 * @since 1.20
 */
class ActivityVueAsset extends VueAssetBundle
{
    /**
     * @inheritdoc
     */
    public string $moduleId = 'activity';

    /**
     * @inheritdoc
     *
     * The components an entry nests by tag: `<UserImage>` for the author, `<SpaceImage>` for the space badge.
     */
    public $depends = [
        SpaceVueAsset::class,
        UserVueAsset::class,
    ];
}
