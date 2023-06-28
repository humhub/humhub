<?php

/**
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\file\models\AttachedImageOwnerInterface;
use humhub\modules\file\models\File;

/**
 * ContentImage is responsible for all profile images.
 *
 * This class handles all tasks related to profile images.
 * Will be used for Space or User Profiles.
 *
 * Prefixes:
 *  "" = Resized profile image
 *  "_original" = Original uploaded file
 *
 * @since  1.15
 *
 * @property-read string|ContentContainerActiveRecord|AttachedImageOwnerInterface $container
 */
class ContentImage extends ContentAttachedImage
{
    public ?int $squared = 150;

    public ?string $defaultImage = 'default_user';

    public static ?int $defaultFilterCategory = File::CATEGORY_OG_IMAGE;
}
