<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\models\forms;

trigger_error(
    sprintf(
        'Class %s is deprecated. Please use %s instead.',
        UploadProfileImage::class,
        ContentImageUpload::class
    ),
    E_USER_DEPRECATED
);


/**
 * @since      0.5
 * @deprecated since 1.15
 * @see        ContentImageUpload
 */
class UploadProfileImage extends ContentImageUpload
{
}
