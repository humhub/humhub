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
        } catch (\Exception $ex) {
            return FileHelper::getMimeTypeByExtension($this->file->file_name) ?? $this->file->mime_type;
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

    public function getVariants(array $except = []): array
    {
        return $this->fs->listContents($this->getPath())
            ->filter(fn($f) => $f->isFile())
            ->filter(function ($f) use ($except) {
                $name = basename($f->path());
                if ($name === $this->originalFileName) {
                    return false;
                }
                foreach ($except as $e) {
                    $e = str_replace('*', '', $e);
                    if (str_contains($name, $e)) {
                        return false;
                    }
                }
                return true;
            })
            ->map(fn($f) => basename($f->path()))
            ->toArray();
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
            Yii::error("Could  not write: ". $ex->getMessage());
        }
    }

    public function setByPath(string $path, ?string $variant = null): void
    {
        $this->fs->writeStream($this->get($variant), fopen($path, 'r'), $this->filesystemOptions);
    }

    public function delete($variant = null, array $except = []): void
    {
        $cleanedExcept = array_map(fn($e) => str_replace('*', '', $e), $except);

        $files = $this->fs->listContents($this->getPath(), false)
            ->filter(fn(StorageAttributes $attributes) => $attributes->isFile())
            ->filter(
                fn(StorageAttributes $attribute) => empty(
                    array_filter(
                        $cleanedExcept,
                        fn($e) => str_contains(basename($attribute->path()), $e),
                    )
                ),
            )
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
