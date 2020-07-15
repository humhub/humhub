<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;


use humhub\modules\file\models\File;
use Imagine\Image\ImageInterface;

/**
 * Class ImageHelper
 *
 * @since 1.5.2
 * @package humhub\modules\file\libs
 */
class ImageHelper
{

    /**
     * Fix orientation of JPEG images based on EXIF information
     *
     * @see https://github.com/yiisoft/yii2-imagine/issues/44
     * @param $image ImageInterface
     * @param $file File|string
     * @throws \yii\base\InvalidConfigException
     */
    public static function fixJpegOrientation($image, $file)
    {
        $mimeType = '';
        if ($file instanceof File) {
            $mimeType = $file->mime_type;
            $file = $file->store->get();
        } elseif (is_string($file) && file_exists($file)) {
            $mimeType = FileHelper::getMimeType($file);
        }

        if ($mimeType === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($file);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $image->rotate(180);
                        break;
                    case 6:
                        $image->rotate(90);
                        break;
                    case 8:
                        $image->rotate(-90);
                        break;
                }
            }
        }
    }
}
