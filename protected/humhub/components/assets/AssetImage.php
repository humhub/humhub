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
use yii\helpers\Json;
use yii\helpers\StringHelper;
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
     * Length of the token signature in bytes, see {@see signToken()}.
     */
    private const TOKEN_MAC_LENGTH = 16;

    /**
     * Aliases the default file is stored relative to in a token, most specific first.
     */
    private const TOKEN_ALIASES = ['@humhub', '@webroot'];

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
     * Returns the URL of the image.
     *
     * A relative URL points to the published copy in the assets, which are volatile: they are
     * removed on every cache flush (so after every update), by a manual deletion of the `assets`
     * directory or by a redeployment, and republished on the next render.
     *
     * An absolute URL (`$scheme`) is meant to leave the platform, e.g. in emails or Open Graph
     * tags, where it is never rendered again. It therefore points to
     * {@see \humhub\controllers\AssetImageController} instead, which republishes the image if
     * needed and redirects to its current URL.
     *
     * @param array|null $options use `null` use default options, `[]` get unmodified file. All available options in class header
     * @param bool $scheme whether to return an absolute URL that stays valid as long as the image exists
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

        if ($scheme && ($token = $this->createToken($options)) !== null) {
            return Url::to(['/asset-image', 'i' => $token], true);
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
     * Creates the AssetImage referenced by a token of {@see getUrl()}.
     *
     * @return array|null [AssetImage, options], or `null` if the token is invalid
     * @since 1.19.1
     */
    public static function fromToken(string $token): ?array
    {
        $key = self::getTokenKey();
        $raw = StringHelper::base64UrlDecode($token);
        if ($key === null || strlen($raw) <= self::TOKEN_MAC_LENGTH) {
            return null;
        }

        $data = substr($raw, self::TOKEN_MAC_LENGTH);
        if (!hash_equals(self::signToken($data, $key), substr($raw, 0, self::TOKEN_MAC_LENGTH))) {
            return null;
        }

        $decoded = json_decode($data, true);
        $file = $decoded[0] ?? null;
        $options = $decoded[1] ?? null;
        $defaultFile = $decoded[2] ?? null;
        if (!is_string($file) || $file === '' || !is_array($options) || ($defaultFile !== null && !is_string($defaultFile))) {
            return null;
        }

        return [new static(['file' => $file, 'defaultFile' => $defaultFile]), $options];
    }

    /**
     * The token identifies the image and its variant. It is signed, since `file` is a path on the
     * data mount, which also holds private files.
     *
     * @return string|null `null` if no secret is set yet (e.g. during the installation)
     */
    private function createToken(array $options): ?string
    {
        $key = self::getTokenKey();
        if ($key === null) {
            return null;
        }

        $data = Json::encode([$this->file, $options, $this->getPortableDefaultFile()]);

        return StringHelper::base64UrlEncode(self::signToken($data, $key) . $data);
    }

    /**
     * Raw HMAC truncated to 128 bits: the token is part of a URL, so every byte counts, and
     * 128 bits are far beyond what forging a token would require.
     */
    private static function signToken(string $data, string $key): string
    {
        return substr(hash_hmac('sha256', $data, $key, true), 0, self::TOKEN_MAC_LENGTH);
    }

    /**
     * Key derived from the installation secret for this purpose only. The secret itself also
     * validates cookies, so nothing signed for another purpose is ever a valid token.
     *
     * @return string|null `null` if no secret is set yet (e.g. during the installation)
     */
    private static function getTokenKey(): ?string
    {
        $secret = Yii::$app->settings->get('secret');
        if (!$secret) {
            return null;
        }

        return Yii::$app->security->hkdf('sha256', $secret, null, 'asset-image');
    }

    /**
     * The default file relative to the first matching alias of {@see TOKEN_ALIASES}: keeps the
     * token short and independent of the installation path, which may change (e.g. on a
     * redeployment).
     *
     * In web requests `@webroot` is the script directory, which may be a symlink to the
     * installation (e.g. a `current` release link), while default files are usually built from
     * resolved paths, so the resolved alias path is matched as well.
     */
    private function getPortableDefaultFile(): ?string
    {
        if (!$this->defaultFile) {
            return null;
        }

        foreach (self::TOKEN_ALIASES as $alias) {
            $aliasPath = Yii::getAlias($alias, false);
            if (!$aliasPath) {
                continue;
            }

            foreach (array_unique([$aliasPath, realpath($aliasPath)]) as $path) {
                if ($path && str_starts_with($this->defaultFile, $path . '/')) {
                    return $alias . substr($this->defaultFile, strlen($path));
                }
            }
        }

        return $this->defaultFile;
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
