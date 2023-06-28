<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\converter;

use humhub\modules\file\libs\ImageHelper;
use humhub\modules\file\models\File;
use humhub\modules\file\Module;
use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\imagine\Image;

/**
 * PreviewImage Converter
 *
 * @since 1.2
 * @author Luke
 * @codingStandardsIgnoreFile PSR2.Methods.MethodDeclaration.Underscore
 * @noinspection MissingPropertyAnnotationsInspection
 */
class PreviewImage extends BaseConverter
{
    public bool $failOnError = false;

    protected ?string $id = null;

    /**
     * @var ImageInterface|null
     */
    private ?ImageInterface $_image = null;

    /**
     * @var string|null
     */
    private ?string $_imageFile = null;

    /**
     * @var array|null = [
     * 'path' => string,        // $destination
     * 'format' => string,      // $format
     * 'mimeType' => string,    // $mime_type
     * 'width' => int,          // $box->getWidth()
     * 'height' => int,         // $box->getHeight()
     * 'size' => int,           // $size
     * 'hash_sha1' => string,   // $hash
     * 'error' => int|false,    // error code
     * 'message' => string,     // error message
     * ]
     */
    public ?array $result = null;

    /**
     * @param array $config = [
     *      'file' => File::class,
     *      'options' => [
     *          'height' => 0,
     *          'width' => 0,
     *          'source' => '',
     *          'save' => [
     *              'format' => 'png',
     *           ],
     *          'thumbnail' => [
     *              'settings' => ManipulatorInterface::THUMBNAIL_INSET,
     *              'filter' => ImageInterface::FILTER_UNDEFINED,
     *          ],
     *          'box' => Box::class,
     *      ],
     *      'failOnError' => false,
     *      'id' => string,
     *      'convert' => false
     * ]
     * @throws ErrorException
     * @throws Exception
     * @throws InvalidConfigException
     * @throws InvalidFileGuid
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if ($config['convert'] ?? false) {
            $this->convert();
        }
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        $this->options['width'] ??= $module->imagePreviewMaxWidth;
        $this->options['height'] ??= $module->imagePreviewMaxHeight;
        $this->options['save']['format'] ??= $module->imagePreviewFormat;

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->id ??= sprintf('preview-image-%sx%s', $this->options['width'], $this->options['height']);
    }

    /**
     * @param string|null $id
     * @return PreviewImage
     * @since 1.15
     */
    public function setId(?string $id): PreviewImage
    {
        $this->id = $id;

        return $this;
    }

    public function render(?File $file = null): string
    {
        if ($file) {
            $this->applyFile($file);
        }

        return Html::img($this->getUrl(), ['class' => 'animated fadeIn', 'alt' => $this->getAltText()]);
    }

    protected function getAltText(?File $file = null): string
    {
        if ($file ??= $this->file) {
            $text = $file->getMetadata()->{File::WELL_KNOWN_METADATA_IMG_ALT_TEXT} ?? $file->title
                ?: $file->file_name;

            return Html::encode($text);
        }

        return '';
    }

    /**
     * @inheritdoc
     * @param string|null $fileName
     * @return $this
     * @throws Exception
     * @throws InvalidFileGuid
     * @throws ErrorException
     * @throws InvalidConfigException
     */
    protected function convert(?string $fileName = null): PreviewImage
    {
        if ($this->file && !is_file($target = $this->file->store->get($fileName ?? $this->getId()))) {

            /** @var Module $module */
            $module = Yii::$app->getModule('file');

            $saveOptions = $this->options['save'];
            $saveOptions['format'] ??= $module->imagePreviewFormat;

            $this->result = ImageHelper::downscaleImage($this->file, [
                'source'=> $this->file->store->get($this->options['source'] ?? null),
                'destination' => $target,
                'box' => $this->options['box'] ?? null,
                'width' =>  $this->options['width'],
                'height' =>  $this->options['height'],
                'save' => $saveOptions,
                'animate' => true,
                'filter' => $this->options['filter'] ?? ImageInterface::FILTER_UNDEFINED,
                'thumbnail' => $this->options['thumbnail'] ?? ManipulatorInterface::THUMBNAIL_INSET,
                'failOnError' => $this->failOnError,
                'updateAttributes' => $this->options['updateAttributes'] ?? false,
            ]);
        }

        return $this;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function canConvert(File $file): bool
    {
        if (strpos($file->mime_type, 'image/') !== 0 || !$file->store->has($this->options['source'] ?? null)) {
            return false;
        }

        return true;
    }

    /**
     * @return int the image width or 0 if not valid
     * @throws Exception
     * @throws InvalidFileGuid
     * @deprecated since 1.5
     */
    public function getWidth(): int
    {
        if ($this->getImage() !== null) {
            return $this->getImage()->getSize()->getWidth();
        }

        return 0;
    }

    /**
     * @return int the image height or 0 if not valid
     * @throws Exception
     * @throws InvalidFileGuid
     * @deprecated since 1.5
     */
    public function getHeight(): int
    {
        if ($this->getImage() !== null) {
            return $this->getImage()->getSize()->getHeight();
        }

        return 0;
    }

    /**
     * @return ImageInterface
     * @throws InvalidFileGuid
     * @throws Exception
     * @deprecated since 1.5
     */
    public function getImage()
    {
        $fileName = $this->file->store->get($this->getFilename());
        if ($this->_image === null || $fileName !== $this->_imageFile) {
            $this->_image = Image::getImagine()->open($fileName);
            $this->_imageFile = $fileName;
        }

        return $this->_image;
    }

    /**
     * Returns the gallery link to the original file
     *
     * @param array|null $htmlOptions optional link html options
     * @return string the link
     */
    public function renderGalleryLink(?array $htmlOptions = []): string
    {
        return Html::a(
            $this->render(),
            $this->file->getUrl(),
            array_merge($htmlOptions ?? [], ['data-ui-gallery' => 'gallery-' . $this->file->guid])
        );
    }
}
