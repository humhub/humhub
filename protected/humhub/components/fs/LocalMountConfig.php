<?php

namespace humhub\components\fs;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Yii;
use yii\base\InvalidArgumentException;

class LocalMountConfig implements MountConfigInterface
{
    public string $path = '';

    public string $baseUrl = '';

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function getFileSystemAdapter(): FileSystemAdapter
    {
        if (empty($this->path)) {
            throw new InvalidArgumentException('Base path must be set.');
        }

        $root = Yii::getAlias($this->path);

        return new LocalFilesystemAdapter($root);
    }
}
