<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\modules\content\models\ContentBanner;

trigger_error(
    sprintf(
        'Class %s is deprecated. Please use %s instead.',
        ProfileBannerImage::class,
        ContentBanner::class
    ),
    E_USER_DEPRECATED
);

/**
 * @since      0.5
 * @deprecated since 1.15
 * @see        ContentBanner
 */
class ProfileBannerImage extends ContentBanner
{
}
