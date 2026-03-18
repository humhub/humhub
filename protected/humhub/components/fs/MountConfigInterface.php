<?php

namespace humhub\components\fs;

use League\Flysystem\FilesystemAdapter;

interface MountConfigInterface
{
    public function getBaseUrl(): ?string;
    public function getFileSystemAdapter(): FileSystemAdapter;
}
