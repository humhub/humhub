<?php

namespace humhub\components\assets;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Visibility;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

class AssetManager extends \yii\web\AssetManager
{
    public $appendTimestamp = true;

    /**
     * Cache key holding the current publish-state generation. `clear()` bumps the
     * generation, which invalidates all published entries at once without having
     * to enumerate them.
     */
    private const CACHE_VERSION_KEY = 'assetManager.published.version';
    private const CACHE_KEY_PREFIX = 'assetManager.published';

    private array $filesystemOptions = [
        'visibility' => Visibility::PUBLIC,
        'directory_visibility' => Visibility::PUBLIC,
    ];

    private FileSystem $fs;

    /**
     * Request-local memo of publish states. The authoritative state lives in
     * `Yii::$app->cache` as one entry per published path, so publishing and
     * unpublishing take effect immediately across web and console processes.
     */
    private array $_published = [];

    private ?int $_cacheVersion = null;

    public function init(): void
    {
        parent::init();

        $this->fs = Yii::$app->fs->getAssetsMount();
        $this->baseUrl = Yii::$app->fs->getAssetsMountConfig()->getBaseUrl();

        if (empty($this->baseUrl)) {
            throw new InvalidArgumentException('Base URL must be set.');
        }

        if ($this->linkAssets) {
            throw new InvalidConfigException('Linking assets is not supported.');
        }
    }

    public function publish($path, $options = [])
    {
        $path = Yii::getAlias($path);

        if (empty($options['forceCopy']) && ($published = $this->getPublished($path)) !== null) {
            //Yii::debug("Cached asset '{$path}'", __METHOD__);
            return $published;
        }

        Yii::debug("Publishing asset '{$path}'", __METHOD__);

        return $this->setPublished($path, parent::publish($path, $options));
    }

    protected function publishFile($src)
    {
        $dir = $this->hash($src);
        $fileName = basename($src);
        $dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;
        $dstFile = $dstDir . DIRECTORY_SEPARATOR . $fileName;

        if (
            !$this->fs->fileExists($dstFile)
            || $this->fs->lastModified($dstFile) < @filemtime($src)
        ) {
            $this->fs->writeStream($dstFile, fopen($src, 'r'));
        }

        if ($this->appendTimestamp && ($timestamp = $this->fs->lastModified($dstFile)) > 0) {
            $fileName .= "?v=$timestamp";
        }

        return [$dstFile, $this->baseUrl . "/$dir/$fileName"];
    }

    protected function publishDirectory($src, $options)
    {
        $dstDir = $this->hash($src);

        $forceCopy = !empty($options['forceCopy']) || ($this->forceCopy && !isset($options['forceCopy']));

        if ($forceCopy || !$this->fs->directoryExists($dstDir)) {
            $currentLength = strlen($src);

            /*
            // Causes problem with Theme Rebuild, since Theme.CSS built and copied afterwards.
            if ($this->flySystem->has($dstDir)) {
                $this->flySystem->deleteDirectory($dstDir);
            }

            $folders = FileHelper::findDirectories($src);
            foreach ($folders as $folder) {
                $folder = substr($folder, $currentLength);
                $this->fs->createDirectory($dstDir . $folder, $this->filesystemOptions);
            }
            */

            $files = FileHelper::findFiles($src, $options);
            foreach ($files as $file) {
                $dstFile = substr((string) $file, $currentLength);
                $this->fs->writeStream($dstDir . $dstFile, fopen($file, 'r'), $this->filesystemOptions);
            }
        }

        return [$dstDir, $this->baseUrl . '/' . $dstDir];
    }

    /**
     * Returns the publish state of an AssetImage variant without any filesystem access,
     * or `null` if the variant has not been published yet.
     *
     * @return array|null [$dstFile, $url] as returned by `publishAssetImage()`
     */
    public function getPublishedAssetImage(string $fileNameWithOptions): ?array
    {
        return $this->getPublished($fileNameWithOptions);
    }

    /**
     * Publishes an AssetImage
     * We need a separate method here, because AssetImages may located in another FlySystem.
     */
    public function publishAssetImage(AssetImage $assetImage, string $fileNameWithOptions): array
    {
        if (($published = $this->getPublished($fileNameWithOptions)) !== null) {
            //Yii::debug("Cached asset image '{$fileNameWithOptions}'", __METHOD__);
            return $published;
        }

        //Yii::debug("Published asset image '{$fileNameWithOptions}'", __METHOD__);

        // Remove root dir, and hash e.g. '/uploads/profile_image/', to store all AssetImage types in an individual directory
        $dstDir = '_/' . hash('xxh32', $assetImage->path);
        $dstFile = $dstDir . DIRECTORY_SEPARATOR . basename($fileNameWithOptions);

        $shouldWrite = true;
        try {
            if ($this->fs->fileExists($dstFile)
                && $this->fs->lastModified($dstFile) >= $assetImage->fs->lastModified($fileNameWithOptions)) {
                $shouldWrite = false;
            }
        } catch (\League\Flysystem\FilesystemException $e) {
            Yii::error($e->getMessage());
        }

        if ($shouldWrite) {
            $this->fs->writeStream(
                $dstFile,
                $assetImage->fs->readStream($fileNameWithOptions),
                $this->filesystemOptions,
            );
        }

        return $this->setPublished($fileNameWithOptions, [$dstFile, $this->baseUrl . '/' . $dstFile . '?t=' . time()]);
    }


    public function unpublishAssetImage(AssetImage $assetImage, string $fileNameWithOptions): void
    {
        // Remove root dir, and hash e.g. '/uploads/profile_image/', to store all AssetImage types in an individual directory
        $dstDir = '_/' . hash('xxh32', $assetImage->path);
        $dstFile = $dstDir . DIRECTORY_SEPARATOR . basename($fileNameWithOptions);

        if ($this->fs->fileExists($dstFile)) {
            $this->fs->delete($dstFile);
        }

        $this->removePublished($fileNameWithOptions);
    }

    public function clear()
    {
        // Only remove the contents of the assets mount, not the mount directory itself.
        // Deleting the root would call rmdir() on the mount, which requires write permission
        // on its parent directory - not granted in some setups (e.g. Docker), causing the
        // clear cache action to fail with a "Permission denied" error.
        foreach ($this->fs->listContents('.')->toArray() as $item) {
            if ($item->isDir()) {
                $this->fs->deleteDirectory($item->path());
            } else {
                $this->fs->delete($item->path());
            }
        }

        $this->_cacheVersion = $this->getCacheVersion() + 1;
        Yii::$app->cache->set(self::CACHE_VERSION_KEY, $this->_cacheVersion);
        $this->_published = [];
    }

    private function getPublished(string $key): mixed
    {
        if (array_key_exists($key, $this->_published)) {
            return $this->_published[$key];
        }

        $published = Yii::$app->cache->get($this->buildCacheKey($key));

        return $this->_published[$key] = ($published === false ? null : $published);
    }

    private function setPublished(string $key, mixed $value): mixed
    {
        Yii::$app->cache->set($this->buildCacheKey($key), $value);

        return $this->_published[$key] = $value;
    }

    private function removePublished(string $key): void
    {
        unset($this->_published[$key]);
        Yii::$app->cache->delete($this->buildCacheKey($key));
    }

    private function buildCacheKey(string $key): array
    {
        return [self::CACHE_KEY_PREFIX, $this->getCacheVersion(), $key];
    }

    private function getCacheVersion(): int
    {
        return $this->_cacheVersion ??= (int) (Yii::$app->cache->get(self::CACHE_VERSION_KEY) ?: 1);
    }

    /**
     * Temporary Hack for dynamic CSS Compile
     */
    public function fileWrite($file, $content)
    {
        try {
            $this->fs->write($this->normalizePath($file), $content, $this->filesystemOptions);
        } catch (FilesystemException $e) {
            print $e->getMessage();
            die();
        }
    }

    public function fileExists($file)
    {
        try {
            return $this->fs->has($this->normalizePath($file));
        } catch (FilesystemException $e) {
            print $e->getMessage();
            die();
        }
    }

    public function normalizePath(string $path): string
    {
        return str_starts_with($path, $this->basePath)
            ? substr($path, strlen($this->basePath))
            : $path;
    }
}
