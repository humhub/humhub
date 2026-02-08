<?php

namespace humhub\components\assets;

use humhub\assets\AppAsset;
use humhub\assets\CoreBundleAsset;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\web\AssetBundle;

class AssetManager extends \yii\web\AssetManager
{
    private Filesystem $flySystem;

    /**
     * @var bool if true will prevent `defer` on all asset bundle scripts
     * @since 1.5
     */
    public $preventDefer = false;

    public function init()
    {
        parent::init();

        $adapter = new \League\Flysystem\Local\LocalFilesystemAdapter(Yii::getAlias($this->basePath));
        $this->flySystem = new \League\Flysystem\Filesystem($adapter);

        if ($this->linkAssets) {
            throw new InvalidConfigException('Linking assets is not supported.');
        }
    }

    /**
     * Clears all currently published assets
     */
    public function clear()
    {
        if ($this->basePath == '') {
            return;
        }

        foreach (scandir(realpath($this->basePath)) as $file) {
            if (str_starts_with($file, '.')) {
                continue;
            }
            FileHelper::removeDirectory($this->basePath . DIRECTORY_SEPARATOR . $file);
        }
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
            if ($this->flySystem->fileExists($dstFile)
                && $this->flySystem->lastModified($dstFile) >= @filemtime($src)) {
                $shouldWrite = false;
            }
        } catch (\League\Flysystem\FilesystemException $e) {
        }

        if ($shouldWrite) {
            $this->flySystem->writeStream($dstFile, fopen($src, 'r'));
        }

        return [$dstFile, "{$this->baseUrl}/$dir/$fileName"];
    }

    protected function publishDirectory($src, $options)
    {
        $dstDir = $this->hash($src);
        $currentLength = strlen($src);

        if (!empty($options['forceCopy']) || ($this->forceCopy && !isset($options['forceCopy'])) || !$this->flySystem->has(
            $dstDir,
        )) {
            if ($this->flySystem->has($dstDir)) {
                $this->flySystem->deleteDirectory($dstDir);
            }

            $folders = FileHelper::findDirectories($src);
            foreach ($folders as $folder) {
                $folder = substr($folder, $currentLength);
                $this->flySystem->createDirectory($dstDir . $folder);
            }

            $files = FileHelper::findFiles($src);
            foreach ($files as $file) {
                $dstFile = substr($file, $currentLength);
                $this->flySystem->writeStream($dstDir . $dstFile, fopen($file, 'r'));
            }
        }

        return [$dstDir, $this->baseUrl . '/' . $dstDir];
    }


    public function addAssetFileByContent($file, $content)
    {
        try {
            $this->flySystem->write($file, $content);
        } catch (FilesystemException $e) {
            print $e->getMessage();
            die();
        }
    }

}
