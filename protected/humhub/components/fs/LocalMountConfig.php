<?php

namespace humhub\components\fs;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use Yii;
use yii\base\InvalidArgumentException;

class LocalMountConfig implements MountConfigInterface
{
    public string $path = '';

    public string $baseUrl = '';

    /**
     * @var array Unix permissions applied per visibility, in the format of
     * `PortableVisibilityConverter::fromArray()`.
     *
     * Directories default to 0775 and public files to 0664, like Yii's `FileHelper::createDirectory()`,
     * `AssetManager::$dirMode`, `FileCache::$dirMode` and `FileTarget::$dirMode`, so a file written by one
     * process (e.g. the web server) stays writable by another one of the same group (e.g. cron or queue
     * workers run from the CLI). Private entries only drop the access for other users.
     *
     * Flysystem's own defaults (0700 directories, 0600 private files) lock out the group.
     * @since 1.19
     */
    public array $permissions = [
        'file' => [
            'public' => 0664,
            'private' => 0660,
        ],
        'dir' => [
            'public' => 0775,
            'private' => 0770,
        ],
    ];

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function getFileSystem(): FileSystem
    {
        if (empty($this->path)) {
            throw new InvalidArgumentException('Base path must be set.');
        }

        $root = Yii::getAlias($this->path);

        return new Filesystem(new LocalFilesystemAdapter(
            $root,
            PortableVisibilityConverter::fromArray($this->permissions, Visibility::PUBLIC),
        ));
    }

    public function useTemporaryUrls(): bool
    {
        return false;
    }
}
