<?php

namespace humhub\components\fs;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Yii;
use yii\base\InvalidArgumentException;

class Local extends AbstractFs
{
    public string $path = '';

    protected function getAdapter(): FileSystemAdapter
    {
        if (empty($this->path)) {
            throw new InvalidArgumentException('Base path must be set.');
        }

        $root = Yii::getAlias($this->path);
        return new LocalFilesystemAdapter($root);
    }


}
