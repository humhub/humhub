<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\modules\file\models\File;
use humhub\modules\file\Module;
use Imagine\Image\ImageInterface;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\imagine\Image;

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
     * @throws InvalidConfigException
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

                if ($image instanceof \Imagine\Imagick\Image) {
                    /** @var \Imagine\Imagick\Image $image */
                    $image->getImagick()->setImageOrientation(1);
                }
            }
        }
    }

    /**
     * Scales down a file image if necessary.
     * The limits can be defined in the File Module class.
     *
     * @param $file File
     * @since 1.7
     */
    public static function downscaleImage($file)
    {
        if (substr($file->mime_type, 0, 6) !== 'image/') {
            return;
        }

        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        // Is used to avoid saving without any configured scaling option.
        $isModified = false;

        $imagineOptions = [];
        if ($file->mime_type === 'image/jpeg') {
            if (!empty($module->imageJpegQuality)) {
                $imagineOptions['jpeg_quality'] = $module->imageJpegQuality;
                $isModified = true;
            }
            $imagineOptions['format'] = 'jpeg';
        } elseif ($file->mime_type === 'image/png') {
            if (!empty($module->imagePngCompressionLevel)) {
                $imagineOptions['png_compression_level'] = $module->imagePngCompressionLevel;
                $isModified = true;
            }
            $imagineOptions['format'] = 'png';
        } elseif ($file->mime_type === 'image/webp') {
            if (!empty($module->imageWebpQuality)) {
                $imagineOptions['webp_quality'] = $module->imageWebpQuality;
                $isModified = true;
            }
            $imagineOptions = ['format' => 'webp'];
        } elseif ($file->mime_type === 'image/gif') {
            $imagineOptions = ['format' => 'gif'];
        } else {
            return;
        }

        try {
            $image = Image::getImagine()->open($file->store->get());
        } catch (\Exception $ex) {
            Yii::error('Could not open image ' . $file->store->get() . '. Error: ' . $ex->getMessage(), 'file');
            return;
        }

        static::fixJpegOrientation($image, $file);

        if ($module->imageMaxResolution !== null) {
            $maxResolution = explode('x', $module->imageMaxResolution, 2);
            if (empty($maxResolution)) {
                throw new InvalidConfigException('Invalid max. image resolution configured!');
            }

            if ($image->getSize()->getWidth() > $maxResolution[0]) {
                $image->resize($image->getSize()->widen($maxResolution[0]));
                $isModified = true;
            }

            if ($image->getSize()->getHeight() > $maxResolution[1]) {
                $image->resize($image->getSize()->heighten($maxResolution[1]));
                $isModified = true;
            }
        }

        if ($isModified) {
            $image->save($file->store->get(), $imagineOptions);
            $file->updateAttributes(['size' => filesize($file->store->get())]);
        }

    }

    /**
     * @param string $filePath
     * @return bool
     * @throws Exception
     */
    public static function checkMaxDimensions(string $filePath): bool
    {
        /* @var $module Module */
        $module = Yii::$app->getModule('file');

        // Don't allow to process an image more X megapixels
        if (!empty($module->imageMaxProcessingMP)
            && !empty($filePath)
            && is_file($filePath)
            && ($imageSize = @getimagesize($filePath))
            && isset($imageSize[0], $imageSize[1])
            && $imageSize[0] * $imageSize[1] > $module->imageMaxProcessingMP * 1024 * 1024) {
            throw new Exception('Image more ' . $module->imageMaxProcessingMP . ' megapixels cannot be processed!');
        }

        return true;
    }

}
