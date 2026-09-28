<?php

namespace humhub\components\fs;

use League\Flysystem\Filesystem;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use Yii;
use yii\base\InvalidArgumentException;

class LocalMountConfig implements MountConfigInterface
{
    public string $path = '';

    public string $baseUrl = '';

    /**
     * @var int the permission to be set for newly created directories, like `yii\web\AssetManager::$dirMode`.
     * Applied with `chmod()`, regardless of the process umask, so a directory created by one process (e.g. the
     * web server) stays writable by another one of the same group (e.g. cron or the queue worker run from the CLI).
     * @since 1.19
     */
    public int $dirMode = 0775;

    /**
     * @var int the permission to be set for newly created files, like `yii\web\AssetManager::$fileMode`
     * @since 1.19
     */
    public int $fileMode = 0664;

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
            new PortableVisibilityConverter($this->fileMode, $this->fileMode, $this->dirMode, $this->dirMode),
        ));
    }

    public function useTemporaryUrls(): bool
    {
        return false;
    }
}
