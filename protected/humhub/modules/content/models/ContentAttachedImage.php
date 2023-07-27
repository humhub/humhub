<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\libs\UUID;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\AttachedImage;
use humhub\modules\file\models\AttachedImageOwnerInterface;
use humhub\modules\file\models\File;
use humhub\modules\space\models\Space;
use humhub\modules\ui\view\components\Theme;
use Yii;

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
class ContentAttachedImage extends AttachedImage
{
    public static bool $defaultFilterCategoryAsBitmask = true;

    public function getAspectRatioThemed(Theme $theme)
    {
        return $this->owner instanceof Space
            ? $theme->variable('space-profile-image-ratio', $this->getAspectRatio())
            : $theme->variable('user-profile-image-ratio', $this->getAspectRatio());
    }

    /**
     * @return ContentContainerActiveRecord|AttachedImageOwnerInterface|string
     * @since        1.4
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public function getContainer()
    {
        return $this->getOwner();
    }

    public function getCropAreaThemed(Theme $theme): string
    {
        return $this->owner instanceof Space
            ? $theme->variable('space-profile-image-crop', '0, 0, ' . $this->getWidth() . ', ' . $this->getHeight())
            : $theme->variable('user-profile-image-crop', '0, 0, ' . $this->getWidth() . ', ' . $this->getHeight());
    }

    public function setGuid($guid): AttachedImage
    {
        parent::setGuid($guid);

        // for backwards-compatibility, check if guid is an ActiveContentContainerRecord
        if (
            $this->owner === null && $this->owner = ContentContainerActiveRecord::findOne(
                ['guid' => $this->guid]
            )
        ) {
            $this->normalizeOldRecordImage($this->guid);
        }

        return $this;
    }

    public static function getImageOwnerClass(): string
    {
        return ContentContainer::class;
    }

    /**
     * Finds an old Image and moves it to the new structure as of 1.15
     *
     * @param string $guid
     * @param string $folder_images
     * @return void
     * @since 1.15
     * @deprecated since 1.15. Used for migration purposis only
     */
    protected function normalizeOldRecordImage(string $guid, $folder_images = 'profile_image')
    {
        $path = rtrim(Yii::getAlias('@webroot/uploads/' . $folder_images), "\\/");

        if (!is_dir($path)) {
            return;
        }

        $this->guid = UUID::v4();

        $store = $this->getStore()->setFile($this);

        foreach ([null, '_org'] as $variant) {
            $oldPath = sprintf('%s/%s%s.jpg', $path, $guid, $variant);

            if (!is_file($oldPath)) {
                continue;
            }

            $newPath = $store->get($variant);
            rename($oldPath, $newPath);
        }

        foreach (FileHelper::findFiles($path, ['recursive' => false]) as $f) {
            $variant = preg_match("@$guid(.+)\.jpg\$@", $f, $matches)
                ? $matches[1]
                : $matches[0];
            $newPath = $store->get($variant);
            rename($oldPath, $newPath);
        }

        if (empty(FileHelper::findDirectories($path))) {
            FileHelper::removeDirectory($path);
        }
    }
}
