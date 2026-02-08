<?php

namespace mikk150\assetmanager;

use creocoder\flysystem\Filesystem;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\FileHelper;

class AssetManager extends \yii\web\AssetManager
{
    /**
     * @var string the root directory storing the published asset files.
     */
    public $basePath;
    /**
     * @var string the base URL through which the published asset files can be accessed.
     */
    public $baseUrl;

    /**
     * @var Filesystem
     */
    public $flySystem;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->flySystem = Instance::ensure($this->flySystem, Filesystem::class);

        if ($this->linkAssets) {
            throw new InvalidConfigException('Linking assets is not supported.');
        }

        if (!$this->basePath) {
            throw new InvalidConfigException('basePath needs to be set.');
        }

        if (!$this->baseUrl) {
            throw new InvalidConfigException('baseUrl needs to be set.');
        }

        Component::init();
    }

    /**
     * @inheritdoc
     */
    protected function publishFile($src)
    {
        $dir = $this->hash($src);
        $fileName = basename($src);
        $dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;
        $dstFile = $dstDir . DIRECTORY_SEPARATOR . $fileName;

        if (!$this->flySystem->has($dstDir)) {
            $this->flySystem->createDir($dstDir);
        }

        try {
            if ($this->flySystem->getTimestamp($dstFile) < @filemtime($src)) {
                $this->flySystem->updateStream($dstFile, fopen($src, 'r'));
            }
        } catch (\League\Flysystem\FileNotFoundException $e) {
            $this->flySystem->writeStream($dstFile, fopen($src, 'r'));
        }

        return [$dstFile, $this->baseUrl . "/$dir/$fileName"];
    }

    /**
     * @inheritdoc
     */
    protected function publishDirectory($src, $options)
    {
        $dir = $this->hash($src);
        $dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;

        $files = FileHelper::findFiles($src);

        $folders = FileHelper::findDirectories($src);

        $currentLength = strlen($src);

        if (!empty($options['forceCopy']) || ($this->forceCopy && !isset($options['forceCopy'])) || !$this->flySystem->has($dstDir)) {
            if ($this->flySystem->has($dstDir)) {
                $this->flySystem->deleteDir($dstDir);
            }
            
            $folders = FileHelper::findDirectories($src);
            foreach ($folders as $folder) {
                $folder = substr($folder, $currentLength);
                $this->flySystem->createDir($dstDir . $folder);
            }

            $files = FileHelper::findFiles($src);
            foreach ($files as $file) {
                $dstFile = substr($file, $currentLength);
                $this->flySystem->writeStream($dstDir . $dstFile, fopen($file, 'r'));
            }
        }

        return [$dstDir, $this->baseUrl . '/' . $dir];
    }
}
