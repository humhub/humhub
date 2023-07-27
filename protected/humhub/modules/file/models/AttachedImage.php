<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2017-2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use humhub\exceptions\InvalidArgumentTypeException;
use humhub\libs\UUID;
use humhub\modules\file\components\AttachedImageStorageManager;
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\file\libs\ImageHelper;
use humhub\modules\ui\view\components\Theme;
use Imagine\Image\ManipulatorInterface;
use Stringable;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\db\IntegrityException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 *
 * @property-write string|UploadedFile $new
 * @property-read float|int $aspectRatio
 * @property-read int $width
 * @property string $folderImages
 * @property-read int $height
 * @property AttachedImageStorageManager $store
 */
class AttachedImage extends AttachedFile
{
    public static string $fileUploadFieldName = 'image';

    /**
     * @inheritdoc
     */
    public $fileUploadAllowedExtensions = 'jpg, jpeg, png, tiff, webp';

    /**
     * @inheritdoc
     */
    public ?string $uploadVariant = '_upload';

    public ?string $storeClass = AttachedImageStorageManager::class;

    /**
     * @var string|null name or file path of the default image
     */
    public ?string $defaultImage;
    /**
     * Extension of the resized images. Can be one of the supported extensions.
     *
     * @var string
     * @since 1.15
     * @see   Imagine
     */
    public string $defaultFormat = 'jpg';

    /**
     * Maximum width of the image. Defaults to 800 to ensure it stays within the layout limits. Set to 0 for no limit.
     *
     * @var int
     * @since 1.15
     */
    public int $maxWidth = 800;

    /**
     * Maximum height of the image. Set to 0 for no limit.
     *
     * @var int
     * @since 1.15
     */
    public int $maxHeight = 0;

    /**
     * Size of the squared version used as the default image. If set to 0, it will be calculated by the minimum of
     * width and height. If set to null, the default image will be unchanged.
     *
     * @var int|null
     * @since 1.15
     */
    public ?int $squared = 0;
    /**
     * @var Integer Width of the image. Must be less or equal to $maxWidth.
     * @see static::$maxWidth
     * @see static::getWidth()
     */
    protected int $width = 150;
    /**
     * @var Integer height of the image. Must be less or equal to $maxHeight.
     * @see static::$maxHeight
     * @see static::getHeight()
     */
    protected int $height = 150;

    /**
     *
     * @param string|AttachedImageIntermediateInterface|AttachedImageOwnerInterface $guid Optional. GUID of the Image.
     * @param string|null $defaultImage . Optional.
     * @param array $config name-value pairs that will
     *                                                                                      be used to initialize the
     *                                                                                      object properties
     *                                                                                      *
     *
     * @throws IntegrityException|Exception
     * @see          File::getStore()
     * @noinspection PhpMissingParamTypeInspection
     */
    public function __construct($guid = null, $defaultImage = null, $config = [])
    {
        /** @noinspection PhpLoopNeverIteratesInspection */
        while (true) {
            if (is_array($guid)) {
                // if $guid is an array, both other parameters must be empty
                if (!empty($defaultImage) && !empty($config)) {
                    throw new InvalidArgumentTypeException(
                        __METHOD__,
                        [1 => '$guid'],
                        [
                            'string',
                            AttachedImageIntermediateInterface::class,
                            AttachedImageOwnerInterface::class,
                            'null'
                        ],
                        $guid
                    );
                }

                // then we assume that $guid is actually $config
                $config = $guid;
                $guid = null;
                break;
            }

            if (is_array($defaultImage)) {
                // if $guid is an array, $config must be empty
                if (!empty($config)) {
                    throw new InvalidArgumentTypeException(
                        __METHOD__,
                        [2 => '$defaultImage'],
                        ['string', 'null'],
                        $guid
                    );
                }

                // then we assume that $defaultImage is actually $config
                $config = $defaultImage;
                $defaultImage = null;
                break;
            }

            if (!ArrayHelper::isTraversable($config)) {
                throw new InvalidArgumentTypeException(
                    __METHOD__,
                    [1 => '$guid'],
                    ["string", AttachedImageIntermediateInterface::class, AttachedImageOwnerInterface::class],
                    $guid
                );
            }

            break;
        }

        $guid ??= $config['guid'] ?? null;
        unset($config['guid']);

        $this->setGuid($guid);

        $defaultImage = $config['defaultImage'] ?? null;
        unset($config['defaultImage']);

        // make sure, $this->defaultImage is initialized
        try {
            $this->defaultImage = $defaultImage ?? $this->defaultImage;
        } catch (Throwable $t) {
            // however, if not, then we set it to null here
            $this->defaultImage = null;
        }

        parent::__construct($config);
    }

    public function beforeSave($insert)
    {
        $category = $this->getAttribute('category');

        $category |= static::$defaultFilterCategory ?? File::CATEGORY_ATTACHED_IMAGE;

        $this->category = $category;

        return parent::beforeSave($insert);
    }

    /**
     * Get aspect ratio
     *
     * @return float
     */
    public function getAspectRatio()
    {
        return $this->getWidth() / $this->getHeight();
    }

    /**
     * @param Theme $theme
     *
     * @return float
     */
    public function getAspectRatioThemed(Theme $theme)
    {
        return $this->getAspectRatio();
    }

    public function getCropAreaThemed(Theme $theme): string
    {
        return '0, 0, ' . $this->getWidth() . ', ' . $this->getHeight();
    }

    /**
     * @return string|null
     * @noinspection PhpUnused
     */
    public function getDefaultImage(): ?string
    {
        return $this->defaultImage;
    }

    /**
     * @param string|null $defaultImage
     *
     * @return AttachedImage
     * @noinspection PhpUnused
     */
    public function setDefaultImage(?string $defaultImage): AttachedImage
    {
        $this->defaultImage = $defaultImage;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->maxHeight
            ? min($this->height, $this->maxHeight)
            : $this->height;
    }

    /**
     * @return int|null
     */
    protected function getSquared(): ?int
    {
        return $this->squared;
    }

    /**
     * Returns the URl of the Modified Profile Image
     *
     * @param string $variant Prefix of the returned image
     * @param boolean $absolute URL Scheme
     *
     * @return String Url of the profile image
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function getUrl(
        $variant = '',
        bool $absolute = false
    ): string {
        $params = $this->getUrlParameters($variant);

        switch (true) {
            case $filePath = $this->getStore()->has($params['variant'] ?? null):
                $params['m'] = filemtime($filePath);

                return parent::getUrl($params);

            case $this->defaultImage === null || $this->defaultImage === '':
                return '';

            /** @noinspection PhpMissingBreakStatementInspection */
            case $this->defaultImage[0] === "@":
                $path = Yii::$app->view->theme->applyTo($this->defaultImage);
                $path = Yii::getAlias($path);

                if ($path === false || !file_exists($path)) {
                    Yii::Warning("Default image $this->defaultImage does not exist at $path");

                    return '';
                }

                $this->defaultImage = $path;

            case file_exists($this->defaultImage):
                $filePath = $this->defaultImage;
                $params['m'] = filemtime($filePath);
                break;

            default:
                $path = '@web-static/img/' . $this->defaultImage;
                $path .= '.jpg';
                $path = Yii::$app->view->theme->applyTo($path);
                return Url::to($path, $absolute);
        }

        if ($filePath !== null) {
            $path = str_replace(Yii::getAlias('@webroot'), Yii::getAlias('@web'), $filePath);
        }

        /** @noinspection PhpUndefinedVariableInspection */
        array_unshift($params, $path);

        return Url::to($params, $absolute);
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->maxWidth
            ? min($this->width, $this->maxWidth)
            : $this->width;
    }

    /**
     * @param string|AttachedImageIntermediateInterface|AttachedImageOwnerInterface $guid
     *
     * @return AttachedImage
     * @throws Exception
     * @throws IntegrityException
     */
    public function setGuid($guid): AttachedImage
    {
        if ($guid instanceof AttachedImageIntermediateInterface) {
            $guid = $guid->findImageOwner();
        }

        if ($guid instanceof AttachedImageOwnerInterface) {
            $this->owner = $guid;
            $this->object_model = get_class($guid);
            $this->object_id = $guid->primaryKey;
            $guid = UUID::v4();
        } elseif ($guid !== null && !is_string($guid) && !$guid instanceof Stringable) {
            throw new InvalidArgumentTypeException(
                __METHOD__,
                [1 => '$guid'],
                [
                    'string',
                    Stringable::class,
                    AttachedImageIntermediateInterface::class,
                    AttachedImageOwnerInterface::class,
                ],
                $guid
            );
        } elseif ($guid === null || '' === ($guid = trim($guid))) {
            $guid = UUID::v4();
        } elseif (!UUID::is_valid($guid)) {
            throw new InvalidArgumentException("Invalid GUID provided: $guid");
        }

        $this->guid = (string)$guid;

        return $this;
    }

    /**
     * Sets a new profile image by given temp file
     *
     * @param string|UploadedFile|File $file CUploadedFile or file path
     *
     * @return static
     * @throws Exception
     * @throws InvalidConfigException
     * @deprecated since 1.15. Use static::setStoredFile() instead.
     * @see        static::setStoredFile()
     */
    public function setNew(
        $file
    ): self {
        return $this->setStoredFile($file);
    }

    protected function beforeNewStoredFile(
        $file,
        bool $skipHistoryEntry
    ): void {
        $path = static::extractPath($file);

        ImageHelper::checkMaxDimensions($path);

        parent::beforeNewStoredFile($file, $skipHistoryEntry);
    }


    protected function afterNewStoredFile(?string $destination, ?string $source = null, ?array $attributes = null)
    {
        $variant = $this->updateMode === self::UPDATE_REPLACE
            ? '_original'
            : '_draft';

        // Convert uploaded image to PNG, fix orientation and remove additional meta information
        $result = ImageHelper::downscaleImage($this, [
            'source' => $destination ??= $this->store->get($this->uploadVariant),
            'destination' => $target = $this->store->get($variant),
            'width' => $this->maxWidth,
            'height' => $this->maxHeight,
            'save' => [
                'format' => 'png',
                'png_compression_level' => 9,
            ],
            'animate' => true,
            'failOnError' => true,
        ]);

        $metadata = $this->metadata;
        $metadata->{File::WELL_KNOWN_METADATA_ORIGINAL_MIMETYPE} = $result['mimeType'];
        $metadata->{File::WELL_KNOWN_METADATA_ORIGINAL_HASH} = $result['hash_sha1'];
        $metadata->{File::WELL_KNOWN_METADATA_ORIGINAL_SIZE} = $result['size'];

        // delete uploaded Image
        if ($destination !== $target) {
            unlink($destination);
        }


        if (null === $squared = $this->getSquared()) {
            // Create default version
            $height = $this->getHeight();
            $width = $this->getWidth();
        } else {
            // Create squared version
            if ($squared === 0) {
                $this->squared = min($this->getWidth(), $this->getHeight());
                $squared = $this->getSquared();
            }
            $width = $height = $squared;
        }

        $review = PreviewImage::create([
            'file' => $this,
            'id' => ($this->updateMode === self::UPDATE_REPLACE ? '' : '_draft_') . $this->store->originalFileName,
            'options' => [
                'source' => $variant,
                'height' => $height,
                'width' => $width,
                'save' => ['format' => $this->defaultFormat],
                'thumbnail' => ManipulatorInterface::THUMBNAIL_OUTBOUND,
            ],
            'failOnError' => true,
        ]);

        parent::afterNewStoredFile($review->getFilename(), $source, $review->result);

        return $this;
    }

    /**
     * Indicates there is a custom profile image
     *
     * @return string|null is there a profile image
     * @throws InvalidConfigException
     */
    public function hasImage(): ?string
    {
        return $this->getStore()
            ->has('_original');
    }

    /**
     * Get height
     *
     * @return int
     * @deprecated since 1.15
     * @see        static::getHeight()
     */
    public function height(): int
    {
        return $this->getHeight();
    }

    /**
     * Renders this profile image
     *
     * @param int $width
     * @param array $cfg
     *
     * @return string
     * @throws InvalidConfigException
     * @throws Throwable
     * @since 1.4
     */
    public function render(
        int $width = 32,
        array $cfg = []
    ): string {
        $owner = $this->owner;

        if (!$owner) {
            return '';
        }

        $cfg['width'] = $width;
        $widgetOptions = ['width' => $width];

        // TODO: improve option handling...
        if (isset($cfg['link'])) {
            $widgetOptions['link'] = $cfg['link'];
            unset($cfg['link']);
        }

        if (isset($cfg['showTooltip'])) {
            $widgetOptions['showTooltip'] = $cfg['showTooltip'];
            unset($cfg['showTooltip']);
        }

        if (isset($cfg['tooltipText'])) {
            $widgetOptions['tooltipText'] = $cfg['tooltipText'];
            unset($cfg['tooltipText']);
        }

        return $owner->renderAttachedImage($widgetOptions, $cfg, $this);
    }

    /**
     * Get width
     *
     * @return int
     * @deprecated Since 1.15
     * @see        static::getWidth()
     */
    public function width(): int
    {
        return $this->getWidth();
    }
}
