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
     * The image types whose dimensions are collected, see [[collectDimensions()]]
     * @since 1.20
     */
    public const DIMENSION_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /**
     * Bytes read for the dimensions: enough for the header of nearly every image, the whole file is read only when
     * they don't suffice
     */
    private const DIMENSION_HEADER_BYTES = 262144;

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
        if ($file instanceof File) {
            if ($file->mime_type !== 'image/jpeg') {
                return;
            }
            try {
                $file = $file->store->getContentStream();
            } catch (\Exception) {
                return;
            }
        } elseif (is_string($file) && file_exists($file)) {
            if (FileHelper::getMimeType($file) !== 'image/jpeg') {
                return;
            }
        } else {
            return;
        }

        if (function_exists('exif_read_data')) {
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

        if (is_resource($file)) {
            fclose($file);
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
        if (!str_starts_with((string) $file->mime_type, 'image/')) {
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
            $imagineOptions['format'] = 'webp';
        } elseif ($file->mime_type === 'image/gif') {
            $imagineOptions = ['format' => 'gif'];
        } else {
            return;
        }

        // Without a quality option only a resize changes the image: no decoding (the slow part of an upload) when the
        // dimensions stored on upload are within the maximum. They are after the EXIF rotation, as compared below
        if (!$isModified) {
            if ($module->imageMaxResolution === null) {
                return;
            }

            $maxResolution = explode('x', (string)$module->imageMaxResolution, 2);
            $dimensions = self::getDimensions($file);
            if ($dimensions !== null && isset($maxResolution[1])
                && $dimensions[0] <= $maxResolution[0] && $dimensions[1] <= $maxResolution[1]) {
                return;
            }
        }

        try {
            $image = Image::getImagine()->load($file->store->getContent());
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
            $file->store->setContent($image->get($imagineOptions['format'], $imagineOptions));
            $file->updateAttributes(['size' => $file->store->fileSize()]);
            // Re-encoded after the EXIF rotation and any resize: the stored image has these dimensions
            try {
                self::storeDimensions($file, $image->getSize()->getWidth(), $image->getSize()->getHeight());
            } catch (\Throwable $e) {
                Yii::warning('Could not store the dimensions of file ' . $file->id . ': ' . $e->getMessage(), 'file');
            }
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

    /**
     * Stores the width and height of an image in its metadata, as the platform shows it: after the EXIF rotation
     * [[fixJpegOrientation()]] applies. Content that can't be measured drops the dimensions of the previous one.
     * Other types are left alone: the dimensions of a video belong to [[MediaHelper]].
     *
     * @since 1.20
     */
    public static function collectDimensions(File $file): void
    {
        if (!in_array($file->mime_type, self::DIMENSION_MIME_TYPES, true)) {
            return;
        }

        try {
            $size = self::readImageSize($file);
            if ($size === null) {
                Yii::info('Could not read the dimensions of file ' . $file->id, 'file');
            } elseif ($file->mime_type === 'image/jpeg' && in_array(self::readExifOrientation($file), [6, 8], true)) {
                $size = [$size[1], $size[0]];
            }

            if ($size !== null) {
                self::storeDimensions($file, $size[0], $size[1]);
            } elseif (self::getDimensions($file) !== null) {
                self::removeDimensions($file);
            }
        } catch (\Throwable $e) {
            Yii::warning('Could not collect the dimensions of file ' . $file->id . ': ' . $e->getMessage(), 'file');
        }
    }

    /**
     * @return array{0: int, 1: int}|null width and height of an image, `null` when not collected (yet)
     * @since 1.20
     */
    public static function getDimensions(File $file): ?array
    {
        $metadata = $file->getAttribute('metadata');
        if ($metadata === null || $metadata === '') {
            return null;
        }

        // Parsed into a copy, the record keeps its attribute as loaded. Runs on every render: metadata that can't be
        // parsed has no dimensions.
        if (!$metadata instanceof Metadata) {
            try {
                $metadata = new Metadata($metadata);
            } catch (\Throwable) {
                return null;
            }
        }

        return isset($metadata->width, $metadata->height) ? [(int)$metadata->width, (int)$metadata->height] : null;
    }

    private static function storeDimensions(File $file, int $width, int $height): void
    {
        FileMetadata::update($file, static function (Metadata $metadata) use ($width, $height): void {
            $metadata->width = $width;
            $metadata->height = $height;
        });
    }

    private static function removeDimensions(File $file): void
    {
        FileMetadata::update($file, static function (Metadata $metadata): void {
            unset($metadata->width, $metadata->height);
        });
    }

    /**
     * @return array{0: int, 1: int}|null
     */
    private static function readImageSize(File $file): ?array
    {
        $stream = $file->store->getContentStream();
        $head = (string)stream_get_contents($stream, self::DIMENSION_HEADER_BYTES);
        fclose($stream);

        $size = @getimagesizefromstring($head);
        if ($size === false && strlen($head) >= self::DIMENSION_HEADER_BYTES) {
            $size = @getimagesizefromstring($file->store->getContent());
        }

        return $size === false || empty($size[0]) || empty($size[1]) ? null : [(int)$size[0], (int)$size[1]];
    }

    private static function readExifOrientation(File $file): int
    {
        if (!function_exists('exif_read_data')) {
            return 1;
        }

        $stream = $file->store->getContentStream();
        $exif = @exif_read_data($stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        return (int)($exif['Orientation'] ?? 1);
    }
}
