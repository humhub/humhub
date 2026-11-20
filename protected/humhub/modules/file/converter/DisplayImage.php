<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\converter;

use Exception;
use humhub\modules\admin\models\Log;
use humhub\modules\file\libs\ImageHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\Module;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Yii;
use yii\imagine\Image;

/**
 * The display version of an image, shown by the media viewer instead of a large original: scaled down to fit
 * `imageDisplayMaxWidth` × `imageDisplayMaxHeight` of the file module, in the original's format.
 *
 * The variant is created on its first request (`DownloadAction`), not while a page renders. An image within the bounds
 * gets none: its display URL serves the original, as it does for every file the converter doesn't apply to (GIF for
 * its animation, SVG, …).
 *
 * @since 1.20
 */
class DisplayImage extends BaseConverter
{
    public const VARIANT = 'display-image';

    /**
     * The formats that are scaled down, each saved in its own format again
     */
    public const MIME_TYPES = [
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private const DEFAULT_QUALITY = 85;

    /**
     * @inheritdoc
     */
    public function init()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        $this->options['width'] = $module->imageDisplayMaxWidth;
        $this->options['height'] = $module->imageDisplayMaxHeight;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return self::VARIANT;
    }

    /**
     * The URL of the display version. Unlike other converters it doesn't convert: the download creates the variant
     *
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->file === null ? '' : $this->file->getUrl(self::VARIANT);
    }

    /**
     * Creates the variant when the image exceeds the bounds and doesn't have it yet
     *
     * @return bool whether the file has the variant now; otherwise its original is the display version
     */
    public function prepare(): bool
    {
        if ($this->file === null || !$this->canConvert($this->file)) {
            return false;
        }

        $this->convert(self::VARIANT);

        return $this->file->store->has(self::VARIANT);
    }

    /**
     * @inheritdoc
     */
    protected function convert($fileName)
    {
        if ($this->file->store->has($fileName)) {
            return;
        }

        try {
            $content = $this->file->store->getContent();

            // The size from the header, so an image within the bounds is not decoded
            $size = @getimagesizefromstring($content);
            if ($size === false || ($size[0] <= $this->options['width'] && $size[1] <= $this->options['height'])) {
                return;
            }

            $image = Image::getImagine()->load($content);
            ImageHelper::fixJpegOrientation($image, $this->file);
            $image = $image->thumbnail(new Box($this->options['width'], $this->options['height']), ImageInterface::THUMBNAIL_INSET);

            $format = self::MIME_TYPES[$this->file->mime_type];
            $this->file->store->setContent($image->get($format, $this->getSaveOptions($format)), $fileName);
        } catch (Exception $ex) {
            $message = 'Could not create the display image of file with id ' . $this->file->id . '. Error: ' . $ex->getMessage();
            if (!Log::find()->where(['message' => $message])->exists()) {
                Yii::warning($message);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function canConvert(File $file)
    {
        return isset(self::MIME_TYPES[$file->mime_type]) && $file->store->has();
    }

    private function getSaveOptions(string $format): array
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        return match ($format) {
            'jpeg' => ['jpeg_quality' => $module->imageJpegQuality ?: self::DEFAULT_QUALITY],
            'webp' => ['webp_quality' => $module->imageWebpQuality ?: self::DEFAULT_QUALITY],
            'png' => $module->imagePngCompressionLevel !== null
                ? ['png_compression_level' => $module->imagePngCompressionLevel]
                : [],
        };
    }
}
