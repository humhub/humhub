<?php

use humhub\libs\ProfileBannerImage;
use Imagine\Image\Box;
use Imagine\Image\ManipulatorInterface;
use yii\db\Migration;
use yii\helpers\FileHelper;
use yii\imagine\Image;

/**
 * Class m240531_123502_create_profile_banner_thumb
 */
class m240531_123502_create_profile_banner_thumb extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $profileBannerImage = new ProfileBannerImage('');
        $dir = $profileBannerImage->getDirectoryPath();
        foreach (scandir($dir) as $file) {
            if (
                $file
                && str_ends_with($file, '.jpg')
                && !str_ends_with($file, '_org.jpg')
                && !str_ends_with($file, ProfileBannerImage::THUMB_PREFIX . '.jpg')
                && !str_starts_with($file, '.')
            ) {
                $path = $dir . $file;
                $mimeType = FileHelper::getMimeType($path);
                if (!str_starts_with($mimeType, 'image/')) {
                    continue;
                }

                $thumbPath = $dir . pathinfo($file, PATHINFO_FILENAME) . ProfileBannerImage::THUMB_PREFIX . '.jpg';
                $image = Image::getImagine()->open($path);
                $image->thumbnail(new Box($profileBannerImage->thumbWidth(), $profileBannerImage->thumbHeight()), ManipulatorInterface::THUMBNAIL_OUTBOUND)
                    ->save($thumbPath, ['quality' => 60]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240531_123502_create_profile_banner_thumb cannot be reverted.\n";

        return false;
    }
}
