<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\libs\Html;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use Yii;

/**
 * ContentBanner is responsible for the profile banner images.
 *
 * This class handles all tasks related to profile images.
 * Will be used for Space or User Profiles.
 *
 * Prefixes:
 *  "" = Resized profile image
 *  "_original" = Original uploaded file
 *
 * @since  1.15
 */
class ContentBanner extends ContentAttachedImage
{
    // protected properties

    public int $width = 1134;
    public int $height = 192;
    public int $maxWidth = 2000;
    public ?int $squared = null;
    public ?string $defaultImage = 'default_banner';

    public static ?int $defaultFilterCategory = File::CATEGORY_BANNER_IMAGE;

    /**
     * @inheritDoc
     */
    public function render($width = 32, array $cfg = []): string
    {
        if (is_int($width)) {
            $width .= 'px';
        }

        Html::addCssStyle($cfg, ['width' => $width]);

        return Html::img($this->getUrl(), $cfg);
    }

    /**
     * @inheritdoc
     */
    protected function normalizeOldRecordImage(string $guid, $folder_images = 'profile_image/banner')
    {
        // use different default value for $folder_images
        parent::normalizeOldRecordImage($guid, $folder_images);

        $parent = dirname(rtrim(Yii::getAlias('@webroot/uploads/' . $folder_images)), '\/');

        if (empty(FileHelper::findDirectories($parent))) {
            FileHelper::removeDirectory($parent);
        }
    }
}
