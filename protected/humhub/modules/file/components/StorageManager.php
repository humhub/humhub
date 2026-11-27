<?php

namespace humhub\modules\file\components;

use Exception;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use League\Flysystem\Filesystem;
use League\Flysystem\StorageAttributes;
use League\Flysystem\Visibility;
use Yii;
use yii\base\Component;
use yii\web\UploadedFile;

class StorageManager extends Component implements StorageManagerInterface
{
    /**
     * @var string file name of the base file (without variant)
     */
    public $originalFileName = 'file';
    private string $pathPrefix = 'file';
    public Filesystem $fs;
    private array $filesystemOptions = [
        'visibility' => Visibility::PRIVATE,
        'directory_visibility' => Visibility::PRIVATE,
    ];

    /**
     * @var File
     */
    protected $file;

    public function init(): void
    {
        parent::init();

        $this->fs = Yii::$app->fs->getDataMount();
    }

    public function has(?string $variant = null): bool
    {
        return $this->fs->fileExists($this->get($variant));
    }

    public function get(?string $variant = null): string
    {
        if ($variant === null) {
            $variant = $this->originalFileName;
        }

        return $this->getPath() . DIRECTORY_SEPARATOR . $variant;
    }

    public function fileSize(?string $variant = null): int
    {
        return $this->fs->fileSize($this->get($variant));
    }

    public function mimeType(?string $variant = null): string
    {
        try {
            return $this->fs->mimeType($this->get($variant));
        } catch (\Exception) {
            return (!empty($this->file->mime_type))
                ? $this->file->mime_type
                : FileHelper::getMimeTypeByExtension($this->file->file_name);
        }
    }

    public function getContentStream(?string $variant = null)
    {
        return $this->fs->readStream($this->get($variant));
    }

    public function getContent($variant = null): string
    {
        return $this->fs->read($this->get($variant));
    }

    public function checksum($variant = null, string $algo = 'sha1'): string
    {
        return $this->fs->checksum($this->get($variant), ['checksum_algo' => $algo]);
    }

    /**
     * @param string[] $patterns names, or prefixes when they end in `*`
     * @since 1.20
     */
    public static function matchesAny(string $name, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $pattern = (string)$pattern;
            if (str_ends_with($pattern, '*') ? str_starts_with($name, substr($pattern, 0, -1)) : $name === $pattern) {
                return true;
            }
        }

        return false;
    }

    public function getVariants(array $except = []): array
    {
        return $this->fs->listContents($this->getPath())
            ->filter(fn($f) => $f->isFile())
            ->filter(function ($f) use ($except) {
                $name = basename($f->path());
                if ($name === $this->originalFileName) {
                    return false;
                }
                return !self::matchesAny($name, $except);
            })
            ->map(fn($f) => basename($f->path()))
            ->toArray();
    }

    /**
     * The variants of the file with their size and time of the last change
     *
     * @return array<string, array{size: int, updatedAt: int}> by name
     * @since 1.20
     */
    public function listVariants(): array
    {
        $variants = [];

        foreach ($this->fs->listContents($this->getPath()) as $attributes) {
            $name = basename($attributes->path());
            if ($attributes->isFile() && $name !== $this->originalFileName) {
                $variants[$name] = [
                    'size' => (int)$attributes->fileSize(),
                    'updatedAt' => (int)$attributes->lastModified(),
                ];
            }
        }

        return $variants;
    }

    public function set(UploadedFile $file, ?string $variant = null): void
    {
        if (is_uploaded_file($file->tempName)) {
            $this->setByPath($file->tempName);
        }
    }

    public function setContent($content, ?string $variant = null): void
    {
        try {
            $this->fs->write($this->get($variant), $content, $this->filesystemOptions);
        } catch (\Exception $ex) {
            Yii::error("Could  not write: " . $ex->getMessage());
        }
    }

    public function setByPath(string $path, ?string $variant = null): void
    {
        $this->fs->writeStream($this->get($variant), fopen($path, 'r'), $this->filesystemOptions);
    }

    public function delete($variant = null, array $except = []): void
    {
        if ($variant !== null) {
            $this->fs->delete($this->get($variant));

            return;
        }

        $files = $this->fs->listContents($this->getPath(), false)
            ->filter(fn(StorageAttributes $attributes) => $attributes->isFile())
            ->filter(fn(StorageAttributes $attribute) => !self::matchesAny(basename($attribute->path()), $except))
            ->map(fn(StorageAttributes $attributes) => $attributes->path())
            ->toArray();

        foreach ($files as $file) {
            $this->fs->delete($file);
        }

        if (empty($except)) {
            $this->fs->deleteDirectory($this->getPath());
        }
    }

    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    protected function getPath(): string
    {
        if ($this->file->guid == '') {
            throw new Exception('File GUID empty!');
        }

        $path = $this->pathPrefix . DIRECTORY_SEPARATOR
            . substr($this->file->guid, 0, 1) . DIRECTORY_SEPARATOR
            . substr($this->file->guid, 1, 1) . DIRECTORY_SEPARATOR
            . $this->file->guid;

        return $path;
    }

}
