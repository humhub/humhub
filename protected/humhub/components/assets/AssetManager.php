<?php

namespace humhub\components\assets;

use humhub\helpers\TrackableArray;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Visibility;
use Yii;
use yii\base\Application;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;

class AssetManager extends \yii\web\AssetManager
{
    public $appendTimestamp = true;

    private array $filesystemOptions = [
        'visibility' => Visibility::PUBLIC,
        'directory_visibility' => Visibility::PUBLIC,
    ];

    private FileSystem $fs;

    public bool $enableCache = true;

    private TrackableArray $_published;

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


        if ($this->enableCache) {
            $this->_published = new TrackableArray(Yii::$app->cache->get('assetManagerPublished') ?: []);

            Yii::$app->on(Application::EVENT_AFTER_REQUEST, function ($event) {
                if ($this->_published->hasChanged() && !Yii::$app->request->isConsoleRequest) {
                    Yii::$app->cache->set('assetManagerPublished', $this->_published->toArray());
                }
            });
        } else {
            $this->_published = new TrackableArray();
        }
    }

    public function publish($path, $options = [])
    {
        $path = Yii::getAlias($path);

        if (isset($this->_published[$path]) && empty($options['forceCopy'])) {
            //Yii::debug("Cached asset '{$path}'", __METHOD__);
            return $this->_published[$path];
        }

        Yii::debug("Publishing asset '{$path}'", __METHOD__);

        return $this->_published[$path] = parent::publish($path, $options);
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
            $fileName = $fileName . "?v=$timestamp";
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
                $dstFile = substr($file, $currentLength);
                $this->fs->writeStream($dstDir . $dstFile, fopen($file, 'r'), $this->filesystemOptions);
            }
        }

        return [$dstDir, $this->baseUrl . '/' . $dstDir];
    }

    /**
     * Publishes an AssetImage
     * We need a separate method here, because AssetImages may located in another FlySystem.
     */
    public function publishAssetImage(AssetImage $assetImage, string $fileNameWithOptions): array
    {
        if (isset($this->_published[$fileNameWithOptions])) {
            //Yii::debug("Cached asset image '{$fileNameWithOptions}'", __METHOD__);
            return $this->_published[$fileNameWithOptions];
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

        return $this->_published[$fileNameWithOptions] = [$dstFile, $this->baseUrl . '/' . $dstFile . '?t=' . time()];
    }


    public function unpublishAssetImage(AssetImage $assetImage, string $fileNameWithOptions): void
    {
        // Remove root dir, and hash e.g. '/uploads/profile_image/', to store all AssetImage types in an individual directory
        $dstDir = '_/' . hash('xxh32', $assetImage->path);
        $dstFile = $dstDir . DIRECTORY_SEPARATOR . basename($fileNameWithOptions);

        if ($this->fs->fileExists($dstFile)) {
            $this->fs->delete($dstFile);
        }

        unset($this->_published[$fileNameWithOptions]);
    }

    public function clear()
    {
        $this->enableCache = false;
        Yii::$app->cache->delete('assetManagerPublished');

        $this->fs->deleteDirectory('.');
    }

    /**
     * Temporary Hack for dynamic CSS Compile
     */
    public function fileWrite($file, $content)
    {
        try {
            $this->fs->write($file, $content, $this->filesystemOptions);
        } catch (FilesystemException $e) {
            print $e->getMessage();
            die();
        }
    }

    public function fileExists($file)
    {
        try {
            return $this->fs->has($file);
        } catch (FilesystemException $e) {
            print $e->getMessage();
            die();
        }
    }
}
