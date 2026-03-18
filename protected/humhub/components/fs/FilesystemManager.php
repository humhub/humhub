<?php

namespace humhub\components\fs;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;

class FilesystemManager extends Component
{
    public string $mountData = 'data';
    public string $mountAssets = 'assets';

    /**
     * @var array<string,MountConfigInterface|array>
     */
    public array $mounts = [];

    /**
     * @var array<string,Filesystem>
     */
    private array $_mountedFileSystem = [];

    public function getMount(string $name): Filesystem
    {
        if (isset($this->_mountedFileSystem[$name])) {
            return $this->_mountedFileSystem[$name];
        }

        $mountConf = $this->getMountConfiguration($name);

        $this->_mountedFileSystem[$name] = new Filesystem($mountConf->getFileSystemAdapter());
        return $this->_mountedFileSystem[$name];
    }

    public function getMountConfiguration(string $name): MountConfigInterface
    {
        if (!isset($this->mounts[$name])) {
            throw new InvalidArgumentException(sprintf('Mount %s not exists!', $name));
        }

        if (is_array($this->mounts[$name])) {
            $this->mounts[$name] = Yii::createObject($this->mounts[$name]);
        }

        return $this->mounts[$name];
    }

    public function getDataMount(): FileSystem
    {
        return $this->getMount($this->mountData);
    }

    public function getDataMountConfig(): MountConfigInterface
    {
        return $this->getMountConfiguration($this->mountData);
    }

    public function getAssetsMount(): FileSystem
    {
        return $this->getMount($this->mountAssets);
    }

    public function getAssetsMountConfig(): MountConfigInterface
    {
        return $this->getMountConfiguration($this->mountAssets);
    }

}
