<?php

namespace humhub\components\assets;

use humhub\assets\AppAsset;
use humhub\assets\CoreBundleAsset;
use humhub\components\fs\AbstractFs;
use League\Flysystem\FilesystemException;
use League\Flysystem\Visibility;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\web\AssetBundle;

class AssetManager extends \yii\web\AssetManager
{
    public $appendTimestamp = true;
    public string $fsMount = 'assets';
    private array $filesystemOptions = [
        'visibility' => Visibility::PUBLIC,
        'directory_visibility' => Visibility::PUBLIC,
    ];
    public bool $preventDefer = false;
    private AbstractFs $fs;

    public function init(): void
    {
        parent::init();

        $this->fs = Yii::$app->fs->disk($this->fsMount);
        $this->baseUrl = $this->fs->baseUrl;

        if (empty($this->baseUrl)) {
            throw new InvalidArgumentException('Base URL must be set.');
        }

        if ($this->linkAssets) {
            throw new InvalidConfigException('Linking assets is not supported.');
        }
    }

    public function clear()
    {
        $this->fs->deleteDirectory('.');
    }

    /**
     * @inheritDoc
     *
     * Adds defer support for non HumHub AssetBundles by $defer property and adds dependency to [[CoreBundleAsset]]
     *
     * @param string $name
     * @param array $config
     * @param bool $publish
     * @return AssetBundle
     * @throws InvalidConfigException
     * @since 1.5
     */
    protected function loadBundle($name, $config = [], $publish = true)
    {
        $bundle = parent::loadBundle($name, $config, $publish);
        $bundleClass = $bundle::class;

        if ($bundleClass !== AppAsset::class
            && !in_array($bundleClass, AppAsset::STATIC_DEPENDS)
            && !in_array($bundleClass, CoreBundleAsset::STATIC_DEPENDS)
            && !is_subclass_of($bundleClass, AssetBundle::class)) {
            // Force dependency to CoreBundleAsset
            array_unshift($bundle->depends, CoreBundleAsset::class);

            // Allows to add defer to non HumHub AssetBundles
            if (property_exists($bundle, 'defer') && $bundle->defer) {
                $bundle->jsOptions['defer'] = 'defer';
            }
        }

        if ($this->preventDefer && isset($bundle->jsOptions['defer'])) {
            unset($bundle->jsOptions['defer']);
        }

        return $bundle;
    }

    public function forcePublish(AssetBundle $bundle, $options = [])
    {
        $options['forceCopy'] = true;

        if ($bundle->sourcePath !== null && !isset($bundle->basePath, $bundle->baseUrl)) {
            $path = Yii::getAlias($bundle->sourcePath);

            if (!is_string($path) || ($src = realpath($path)) === false) {
                throw new InvalidArgumentException("The file or directory to be published does not exist: $path");
            }

            if (is_file($src)) {
                return $this->publishFile($src);
            } else {
                return $this->publishDirectory($src, $options);
            }
        } else {
            $bundle->publish($this);
        }
    }

    protected function publishFile($src)
    {
        // Remove root dir, and hash e.g. '/uploads/profile_image/', to store all profile images in same directory
        $dir = str_replace(Yii::getAlias('@webroot'), '', $src);
        $dir = '_/' . hash('xxh32', dirname($dir));

        $fileName = basename($src);
        $dstFile = $dir . DIRECTORY_SEPARATOR . $fileName;

        $shouldWrite = true;
        try {
            if ($this->fs->fileExists($dstFile)
                && $this->fs->lastModified($dstFile) >= @filemtime($src)) {
                $shouldWrite = false;
            }
        } catch (\League\Flysystem\FilesystemException $e) {
        }

        if ($shouldWrite) {
            $this->fs->writeStream($dstFile, fopen($src, 'r'), $this->filesystemOptions);
        }

        if ($this->appendTimestamp && ($timestamp = $this->fs->lastModified($dstFile)) > 0) {
            $fileName = $fileName . "?v=$timestamp";
        }

        return [$dstFile, "{$this->baseUrl}/$dir/$fileName"];
    }

    protected function publishDirectory($src, $options)
    {
        $dstDir = $this->hash($src);

        $forceCopy = !empty($options['forceCopy']) || ($this->forceCopy && !isset($options['forceCopy']));

        if ($forceCopy || !$this->fs->has($dstDir)) {
            $currentLength = strlen($src);

            /*
            // Causes problem with Theme Rebuild, since Theme.CSS built and copied afterwards.
            if ($this->flySystem->has($dstDir)) {
                $this->flySystem->deleteDirectory($dstDir);
            }
            */

            $folders = FileHelper::findDirectories($src);
            foreach ($folders as $folder) {
                $folder = substr($folder, $currentLength);
                $this->fs->createDirectory($dstDir . $folder, $this->filesystemOptions);
            }

            $files = FileHelper::findFiles($src);
            foreach ($files as $file) {
                $dstFile = substr($file, $currentLength);
                $this->fs->writeStream($dstDir . $dstFile, fopen($file, 'r'), $this->filesystemOptions);
            }
        }

        return [$dstDir, $this->baseUrl . '/' . $dstDir];
    }


    /**
     * Temporary Hack for dynamic CSS Compile
     */
    public function addAssetFileByContent($file, $content)
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
