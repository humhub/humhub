<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\modules\content\models\ContentImage;

trigger_error(
    sprintf(
        'Class %s is deprecated. Please use %s instead.',
        ProfileImage::class,
        ContentImage::class
    ),
    E_USER_DEPRECATED
);


/**
 * @inheritdoc
 * @since      0.5
 * @deprecated since 1.15. Use ContentImage.
 * @see ContentImage
 */
class ProfileImage extends ContentImage
{
}
