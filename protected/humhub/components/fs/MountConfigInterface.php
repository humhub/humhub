<?php

namespace humhub\components\fs;


use League\Flysystem\Filesystem;

interface MountConfigInterface
{
    public function getBaseUrl(): ?string;
    public function getFileSystem(): FileSystem;

    public function useTemporaryUrls(): bool;
}
