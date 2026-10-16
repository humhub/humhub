<?php

namespace humhub\components\assets;

use humhub\modules\file\libs\ImageHelper;
use Imagine\Image\Box;
use Imagine\Image\Format;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;
use League\Flysystem\Filesystem;
use League\Flysystem\Visibility;
use Yii;
use yii\base\Component;
use yii\base\InvalidValueException;
use yii\helpers\Url;
use yii\imagine\Image;
use yii\web\UploadedFile;

/**
 * AssetImage handles the publishing and manipulation of image assets.
 * It facilitates the exposure of protected files (`uploads` dir) via the AssetManager (and when configured a CDN).
 *
 * The class provides built-in image processing capabilities (scaling and cropping) based on a set of transformation options.
 *
 * Available `options` in the configuration array:
 * - `width`/`height`:  Fixed dimensions; the image is scaled and cropped to fit.
 * - `maxWidth`/`maxHeight`: Maximum bounds; the image is downscaled proportionally.
 * - `square`: An integer value to create a fixed-size square crop (e.g., 50 for 50x50).
 *
 * @since v1.19
 */
class AssetImage extends Component implements \Stringable
{
    /**
     * Cache key prefix for the data-mount existence lookup, see {@see exists()}.
     */
    private const EXISTS_CACHE_KEY = 'assetImage.exists';

    /**
     * @var string File with path to the AssetImage
     */
    public string $file;

    /**
     * @var array Default options, when `getUrl` is called without `options` parameter.
     */
    public array $defaultOptions = [];

    /**
     * @var array Options applied once to the original file upon first save/storage.
     */
    public array $masterOptions = [];
    public ?string $defaultFile = null;

    /**
     * @var array|null route (with its parameters) of the permanent URL, pointing to an action that
     * resolves this image again, e.g. `['/asset-image/registry', 'name' => 'logo']`; `null` if the
     * image has no permanent URL ({@see getPermanentUrl()} then falls back to {@see getUrl()}).
     * @since 1.19.1
     */
    public ?array $permanentUrlRoute = null;

    private array $filesystemOptions = [
        'visibility' => Visibility::PUBLIC,
        'directory_visibility' => Visibility::PUBLIC,
    ];
    public string $path;
    private readonly string $fileName;
    public FileSystem $fs;
    private ?bool $fileExists = null;

    public function __construct($config = [])
    {
        parent::__construct($config);

        if (empty($this->file)) {
            throw new InvalidValueException('Asset image file cannot be empty.');
        }

        $this->fs = Yii::$app->fs->getDataMount();
        $this->file = Yii::getAlias($this->file);
        $this->fileName = basename($this->file);
        $this->path = dirname($this->file);
        $this->defaultFile = Yii::getAlias($this->defaultFile);
    }

    /**
     * @param array|null $options use `null` use default options, `[]` get unmodified file. All available options in class header
     * @param bool $scheme
     * @return string the URL, or an empty string if neither an uploaded image nor a `defaultFile` is set
     */
    public function getUrl(?array $options = null, bool $scheme = false): string
    {
        if ($options === null) {
            $options = $this->defaultOptions;
        }

        $fileNameWithOptions = $this->getFileNameWithOptions($options);
        if ($fileNameWithOptions === '') {
            // Neither an uploaded image nor a defaultFile
            return '';
        }

        $scaledFileName = $this->path . DIRECTORY_SEPARATOR . $fileNameWithOptions;

        $published = Yii::$app->assetManager->getPublishedAssetImage($scaledFileName);
        if ($published === null) {
            if (!$this->fs->fileExists($scaledFileName)) {
                $this->convert($scaledFileName, $options);
            }

            $published = Yii::$app->assetManager->publishAssetImage($this, $scaledFileName);
        }

        return Url::to($published[1], $scheme);
    }

    /**
     * Returns an absolute URL that stays valid as long as the image exists.
     *
     * {@see getUrl()} points into the published assets, which are volatile: they are removed on
     * every cache flush (so after every update), by a manual deletion of the `assets` directory or
     * by a redeployment, and republished on the next render. A URL that leaves the platform - in
     * an email, which is never rendered again - must therefore not point there. The permanent URL
     * points to {@see \humhub\controllers\AssetImageController} instead, which publishes the
     * image if needed and redirects to its current URL.
     *
     * The image is always served with its {@see $defaultOptions}: images for a specific purpose
     * (e.g. `Yii::$app->img->mailLogo`) are defined with their own defaults.
     *
     * @return string the URL, or an empty string if neither an uploaded image nor a `defaultFile` is set
     * @since 1.19.1
     */
    public function getPermanentUrl(): string
    {
        if ($this->permanentUrlRoute === null) {
            return $this->getUrl(null, true);
        }

        if (!$this->exists() && !$this->defaultFile) {
            return '';
        }

        return Url::to($this->permanentUrlRoute, true);
    }

    public function setUploadedFile(?UploadedFile $file = null): void
    {
        $this->setByFile($file->tempName);
    }

    public function setByFile(string $tempFileName): void
    {
        $this->delete();
        ImageHelper::checkMaxDimensions($tempFileName);
        $image = Image::getImagine()->open($tempFileName);
        ImageHelper::fixJpegOrientation($image, $tempFileName);
        $this->fs->write($this->file, $image->get($this->getFileFormat()), $this->filesystemOptions);

        $this->fileExists = true;
        $this->setCachedExists(true);

        if (!empty($this->masterOptions)) {
            $this->convert($this->file, $this->masterOptions);
        }
    }

    private function getFileNameWithOptions(array $options): string
    {
        if ($this->exists()) {
            $fileName = $this->fileName;
        } else {
            if (!$this->defaultFile) {
                return '';
            }
            $fileName = basename((string) $this->defaultFile);
        }

        ksort($options);
        $checksum = hash('xxh32', json_encode($options));

        $info = pathinfo($fileName);
        // The source file may have no extension (e.g. a HumHub File is stored as `file`);
        // fall back to the resolved image format so the variant still gets a valid extension.
        $extension = $info['extension'] ?? $this->getFileFormat();
        return $info['filename'] . '_' . $checksum . '.' . $extension;
    }

    private function convert(string $newFileName, array $options = []): bool
    {
        $image = Image::getImagine();
        if ($this->exists()) {
            $image = $image->load($this->fs->read($this->file));
        } else {
            $image = $image->open($this->defaultFile);
        }

        if (isset($options['square'])) {
            $options['width'] = $options['square'];
            $options['height'] = $options['square'];
        }

        if (!empty($options['width']) && !empty($options['height'])) {
            $image = $image->thumbnail(
                new Box($options['width'], $options['height']),
                ManipulatorInterface::THUMBNAIL_OUTBOUND,
            );
        }

        if (isset($options['maxWidth']) && $image->getSize()->getHeight() > $options['maxHeight']) {
            $image = $image->resize($image->getSize()->heighten($options['maxHeight']));
        }

        if (isset($options['maxWidth']) && $image->getSize()->getWidth() > $options['maxWidth']) {
            $image = $image->resize($image->getSize()->widen($options['maxWidth']));
        }

        $this->fs->write($newFileName, $image->get($this->getFileFormat()), $this->filesystemOptions);

        return true;
    }

    public function delete(): void
    {
        try {
            if ($this->fs->fileExists($this->file)) {
                $this->fs->delete($this->file);
            }
        } catch (\Exception $e) {
            Yii::error($e, 'base');
        }

        $this->fileExists = false;
        $this->setCachedExists(false);
        $this->deleteWithOptions();
    }

    public function crop($x, $y, $h, $w)
    {
        $image = Image::getImagine()->load($this->fs->read($this->file))
            ->crop(new Point($x, $y), new Box($w, $h));

        $this->fs->write($this->file, $image->get($this->getFileFormat()), $this->filesystemOptions);

        $this->deleteWithOptions();
    }

    /**
     * Checks whether an uploaded image exists on the data mount.
     *
     * On remote (e.g. S3) mounts this lookup is a network round trip per rendered
     * image, so the result is cached in `Yii::$app->cache` and updated explicitly
     * by {@see setByFile()} and {@see delete()}. Whether the cache is used follows
     * {@see AssetManager::$cachePublishState}; on local mounts every call verifies
     * against the filesystem and stays fully self-healing.
     */
    public function exists(): bool
    {
        if ($this->fileExists === null) {
            $this->fileExists = $this->getCachedExists();
        }

        if ($this->fileExists === null) {
            $this->fileExists = $this->fs->fileExists($this->file);
            $this->setCachedExists($this->fileExists);
        }

        return $this->fileExists;
    }

    private function getCachedExists(): ?bool
    {
        if (!$this->isExistsCacheEnabled()) {
            return null;
        }

        // The state is stored as int (0/1): Yii's cache returns `false` on a miss,
        // so a cached boolean would be indistinguishable from "not cached yet".
        $cached = Yii::$app->cache->get([self::EXISTS_CACHE_KEY, $this->file]);

        return is_int($cached) ? (bool) $cached : null;
    }

    private function setCachedExists(bool $exists): void
    {
        if ($this->isExistsCacheEnabled()) {
            Yii::$app->cache->set([self::EXISTS_CACHE_KEY, $this->file], (int) $exists);
        }
    }

    private function isExistsCacheEnabled(): bool
    {
        $assetManager = Yii::$app->assetManager;

        return $assetManager instanceof AssetManager && $assetManager->cachePublishState;
    }

    private function deleteWithOptions(): void
    {
        $prefix = pathinfo($this->fileName, PATHINFO_FILENAME) . '_';

        foreach ($this->fs->listContents($this->path) as $f) {
            if ($f->isFile() && str_starts_with(basename($f->path()), $prefix)) {
                $this->fs->delete($f->path());
                Yii::$app->assetManager->unpublishAssetImage(
                    $this,
                    $this->path . DIRECTORY_SEPARATOR . basename($f->path()),
                );
            }
        }
    }

    private function getFileFormat(): string
    {
        $extension = strtolower(pathinfo($this->file, PATHINFO_EXTENSION));

        $formatMap = [
            'jpg' => Format::ID_JPEG,
            'jpeg' => Format::ID_JPEG,
            'png' => Format::ID_PNG,
            'gif' => Format::ID_PNG,
            'webp' => Format::ID_WEBP,
        ];

        return $formatMap[$extension] ?? 'png';
    }

    public function __toString(): string
    {
        return $this->getUrl();
    }
}
