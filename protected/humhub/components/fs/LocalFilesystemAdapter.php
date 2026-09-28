<?php

namespace humhub\components\fs;

use League\Flysystem\Config;
use League\Flysystem\Local\LocalFilesystemAdapter as BaseLocalFilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\UnixVisibility\VisibilityConverter;

/**
 * Local Flysystem adapter which applies directory modes with `chmod()`.
 *
 * `League\Flysystem\Local\LocalFilesystemAdapter` creates directories with `mkdir($dir, $mode)` only, so the mode
 * is reduced by the process umask (`0775` becomes `0755` under the usual umask `022`), whereas files are always
 * `chmod()`ed to their exact mode. A directory created by one process is then not writable by another one of the
 * same group, e.g. flushing the cache from the web UI cannot delete directories created from the CLI.
 *
 * Like `yii\helpers\FileHelper::createDirectory()`, this adapter `chmod()`s every directory it creates, but keeps
 * the setgid bit inherited from the parent directory, so group inheritance set up by the administrator still works.
 *
 * @since 1.19
 */
class LocalFilesystemAdapter extends BaseLocalFilesystemAdapter
{
    private PathPrefixer $pathPrefixer;
    private VisibilityConverter $visibilityConverter;

    public function __construct(string $location, ?VisibilityConverter $visibility = null, mixed ...$args)
    {
        $this->visibilityConverter = $visibility ?? new PortableVisibilityConverter();
        $this->pathPrefixer = new PathPrefixer($location, DIRECTORY_SEPARATOR);

        parent::__construct($location, $this->visibilityConverter, ...$args);
    }

    /**
     * @inheritdoc
     */
    protected function ensureDirectoryExists(string $dirname, int $visibility): void
    {
        if (is_dir($dirname)) {
            return;
        }

        $parent = dirname($dirname);
        if ($parent !== $dirname && !is_dir($parent)) {
            $this->ensureDirectoryExists($parent, $visibility);
        }

        error_clear_last();

        if (!@mkdir($dirname, $visibility)) {
            $mkdirError = error_get_last();
        }

        clearstatcache(true, $dirname);

        if (!is_dir($dirname)) {
            throw UnableToCreateDirectory::atLocation($dirname, $mkdirError['message'] ?? '');
        }

        // Apply the mode regardless of the umask, keeping the setgid bit inherited from the parent directory.
        // Failures are ignored, like Yii does for its own directories, e.g. on filesystems without chmod() support.
        @chmod($dirname, $visibility | (fileperms($dirname) & 02000));
    }

    /**
     * @inheritdoc
     */
    public function createDirectory(string $path, Config $config): void
    {
        $location = $this->pathPrefixer->prefixPath($path);

        if (is_dir($location)) {
            // Only applies the mode to the existing directory
            parent::createDirectory($path, $config);
            return;
        }

        $visibility = $config->get(Config::OPTION_VISIBILITY, $config->get(Config::OPTION_DIRECTORY_VISIBILITY));

        $this->ensureDirectoryExists(
            $location,
            $visibility === null
                ? $this->visibilityConverter->defaultForDirectories()
                : $this->visibilityConverter->forDirectory($visibility),
        );
    }
}
