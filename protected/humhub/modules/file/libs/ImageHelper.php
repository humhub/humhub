<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

use humhub\exceptions\InvalidArgumentTypeException;
use humhub\modules\admin\models\Log;
use humhub\modules\file\exceptions\InvalidFileGuidException;
use humhub\modules\file\exceptions\MimeTypeNotSupportedException;
use humhub\modules\file\exceptions\MimeTypeUnknownException;
use humhub\modules\file\models\File;
use humhub\modules\file\Module;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
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
     *
     * @param $image ImageInterface
     * @param $file File|string
     * @param $mimeType string|null
     *
     * @throws InvalidConfigException when the `fileinfo` PHP extension is not installed and `$checkExtension` is `false`.
     * @throws InvalidFileGuidException when the File::$guid property is empty
     * @throws Exception when the directory for the file could not be created
     */
    public static function fixJpegOrientation(ImageInterface $image, $file, ?string $mimeType = null)
    {
        if ($file instanceof File) {
            $mimeType ??= $file->mime_type;
            $file = $file->getStore()->get();
        } elseif (!$mimeType && is_string($file) && file_exists($file)) {
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
     * @param $config array = [
     *      'source' => ImagineInterface|string,    // Where to get the image from. Defaults to $file's default variant.
     *      'destination' => string,                // Save file to destination, defaulting to $source/$file's default variant
     *      'mimeType' => string|null,              // Mime-Type of processed file. Defaults to $file->mime_type
     *      'updateAttributes' => true,
     *      'height' => int|null,                   // @ see Module::$ imageMaxResolution
     *      'width' => int|null,                    // @ see Module::$ imageMaxResolution
     *      'filter' => string,                     // Default: ImageInterface::FILTER_UNDEFINED. @ see ImageInterface::resize()
     *      'save' => [
     *          'format' => 'png',
     *       ],
     *      'animate' => bool,                      // Convert to animated GIF. Default: false.
     *      'thumbnail' => int|null,                // Default: ManipulatorInterface::THUMBNAIL_INSET,
     *      'imageStripMetadata' => bool|null,      // @ see Module::$ imageStripMetadata
     *      'failOnError' => false,
     * ]
     *
     * @return array|null = [
     *      'path' => string,        // $destination
     *      'format' => string,      // $format
     *      'mimeType' => string,    // $mime_type
     *      'width' => int,          // $box->getWidth()
     *      'height' => int,         // $box->getHeight()
     *      'size' => int,           // $size
     *      'hash_sha1' => string,   // $hash
     *      'error' => int|false,    // error code
     *      'message' => string,     // error message
     * ]
     * @throws InvalidConfigException when the `fileinfo` PHP extension is not installed and `$checkExtension` is `false`.
     * @throws InvalidArgumentTypeException when $config['source'] is not `null` and not a string
     * @throws MimeTypeNotSupportedException when the given mime-type cannot be converted, i.e., the mime-type does not starte with "image/"
     * @throws MimeTypeUnknownException when the mime-type starts with "image/" but is unknown/not implemented
     * @throws InvalidFileGuidException when the File::$guid property is empty
     * @throws Exception when the directory for the file could not be created
     * @since 1.7
     */
    public static function downscaleImage(File $file, array $config = []): ?array
    {
        try {
            $source = $config['source'] ?? null;

            if ($source instanceof ImagineInterface) {
                $image = $source;
            } elseif ($source !== null && !is_string($source)) {
                throw new InvalidArgumentTypeException(
                    '$source',
                    ['string', ImagineInterface::class],
                    $source
                );
            } else {
                $source = $file->store->get($source);

                try {
                    $mime_type ??= $config['mimeType'] ?? FileHelper::getMimeType($source);
                } catch (InvalidConfigException $e) {
                    $mime_type = "unknown";
                }

                if (!str_starts_with($mime_type ?? '', 'image/')) {
                    throw new MimeTypeNotSupportedException("Cannot convert mime-type '$mime_type'", 1);
                }

                $image = Image::getImagine()->open($source);
            }

              /** @var Module $module */
            $module = Yii::$app->getModule('file');
            $saveOptions = $config['save'] ?? [];
            $format = $saveOptions['format'] ?? null;
            $mime_type ??= "unknown";

            if ($format === null) {
                switch ($mime_type) {
                    case 'image/jpeg';
                        $format = 'jpeg';
                        break;

                    case 'image/png':
                        $format = 'png';
                        break;

                    case 'image/webp':
                        $format = 'webp';
                        break;

                    case 'image/gif':
                        $format = 'gif';
                        break;

                    default:
                        throw new MimeTypeUnknownException("Unknown mime-type '$mime_type'", 2);
                }
            }

            if (($config['animate'] ?? false) && !$image instanceof \Imagine\Gd\Image && count($image->layers()) > 1) {
                $format = 'gif';
                $saveOptions['animated'] = true;
            }

            switch ($format) {
                case 'jpg':
                case 'jpeg':
                    if (!empty($module->imageJpegQuality)) {
                        $saveOptions['jpeg_quality'] = $module->imageJpegQuality;
                    }
                    $format = 'jpeg';
                    break;

                case 'png':
                    if (!empty($module->imagePngCompressionLevel)) {
                        $saveOptions['png_compression_level'] = $module->imagePngCompressionLevel;
                    }
                    break;

                case 'webp':
                    if (!empty($module->imageWebpQuality)) {
                        $saveOptions['webp_quality'] = $module->imageWebpQuality;
                    }
                    break;

                case 'gif':
                    break;

                default:
                    return null;
            }

            $saveOptions['format'] = $format;

            static::fixJpegOrientation($image, $file);

            if ($module->imageMaxResolution !== null) {
                $maxResolution = explode('x', $module->imageMaxResolution, 2);
                if (empty($maxResolution)) {
                    throw new InvalidConfigException('Invalid max. image resolution configured!');
                }
            } else {
                $maxResolution = [PHP_INT_MAX, PHP_INT_MAX];
            }

            $box = $image->getSize();

            $curWidth = $box->getWidth();
            $curHeight = $box->getHeight();

            $maxWidth = min($curWidth, $maxResolution[0]);
            $maxHeight = min($curHeight, $maxResolution[1]);

            $width = $config['width'] ?? 0;
            $height = $config['height'] ?? 0;

            // don't make it larger than it already is
            $width = min($width, $curWidth);
            $height = min($height, $curHeight);

            $width = $width
                ?: $curWidth;
            $height = $height
                ?: $curHeight;

            // don't make it larger than the maximum allowed value
            $width = min($width, $maxWidth);
            $height = min($height, $maxHeight);

            $doResize = false;

            if ($width < $curWidth) {
                $box = new Box($width, $curHeight);
                $doResize = true;
            }

            if ($height < $curHeight) {
                $box = new Box($box->getWidth(), $height);
                $doResize = true;
            }

            if ($doResize) {
                // thumbnail will also strip metadata, apart from profile information
                $image->thumbnail(
                    $box,
                    ManipulatorInterface::THUMBNAIL_FLAG_NOCLONE | ($config['thumbnail'] ?? ManipulatorInterface::THUMBNAIL_INSET),
                    $config['filter'] ?? ImageInterface::FILTER_UNDEFINED
                );
                $box = $image->getSize();
            } else {
                $image->strip();
            }

            $destination = $config['destination'] ?? $source ?? $file->store->get();

            $image->save($destination, $saveOptions);
            $size = filesize($destination);
            $hash = sha1_file($destination);

            if (is_string($source) && ($config['updateAttributes'] ?? true)) {
                $file->updateAttributes([
                    'size' => $size,
                    'hash_sha1' => $hash,
                ]);
            }

            return [
                'path' => $destination,
                'format' => $format,
                'mimeType' => $mime_type,
                'width' => $box->getWidth(),
                'height' => $box->getHeight(),
                'size' => $size,
                'hash_sha1' => $hash,
                'error' => false,
                'resultType' => __METHOD__,
            ];
        } catch (InvalidConfigException | InvalidArgumentTypeException | MimeTypeNotSupportedException | MimeTypeUnknownException | Exception $ex) {
            $message = 'Could not convert file with id ' . ($file->guid ?: $file->id) . '. Error: ' . $ex->getMessage();
            $count = Log::find()->where(['message' => $message])->count();

            if ($count === 0) {
                Yii::warning($message);
            }

            if ($config['failOnError'] ?? false) {
                throw $ex;
            }

            return [
                'error' => $ex->getCode() ?: true,
                'message' => $ex->getMessage(),
            ];
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
        if (
            !empty($module->imageMaxProcessingMP) &&
            !empty($filePath) &&
            is_file($filePath) &&
            ($imageSize = @getimagesize($filePath)) &&
            isset($imageSize[0], $imageSize[1]) &&
            $imageSize[0] * $imageSize[1] > $module->imageMaxProcessingMP * 1024 * 1024
        ) {
            throw new Exception('Image more ' . $module->imageMaxProcessingMP . ' megapixels cannot be processed!');
        }

        return true;
    }
}
