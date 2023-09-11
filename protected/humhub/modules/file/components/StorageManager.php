<?php

/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\components;

use humhub\exceptions\InvalidArgumentValueException;
use humhub\modules\file\exceptions\InvalidFileGuidException;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\libs\Metadata;
use humhub\modules\file\models\File;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * StorageManager for File records
 *
 * @property-read string $path
 * @since  1.2
 * @author Luke
 */
class StorageManager extends Component implements StorageManagerInterface
{
    /**
     * @var string file name of the base file (without variant)
     */
    public string $originalFileName = 'file';

    /**
     * @var string storage base path
     */
    protected string $storagePath = '@filestore';

    /**
     * @var integer file mode
     */
    public int $fileMode = 0644;

    /**
     * @var integer file mode
     */
    public int $dirMode = 0744;

    /**
     * @var File
     */
    protected File $file;

    /**
     * @inheritdoc
     * @throws Exception when the directory for the file could not be created
     * @throws InvalidFileGuidException when the File::$guid property is empty
     */
    public function has($variant = null): ?string
    {
        try {
            $path = $this->get($variant);
        } catch (InvalidFileGuidException $e) {
            return null;
        }

        return is_file($path)
            ? $path
            : null;
    }

    /**
     * @inheritdoc
     * @throws Exception when the directory for the file could not be created
     * @throws InvalidFileGuidException when the File::$guid property is empty
     */
    public function get($variant = null): string
    {
        if ($variant === null || '' === $variant = trim($variant)) {
            $variant = $this->originalFileName;
        } elseif (false !== strpos($variant, DIRECTORY_SEPARATOR)) {
            $path = $this->getPath();
            if (!str_starts_with($variant, $path)) {
                throw new InvalidArgumentValueException('$variant', ['variant name without directory separator', 'full qualified path within stored directory'], $variant);
            }
            return $variant;
        }

        return $this->getPath() . DIRECTORY_SEPARATOR . $variant;
    }

    /**
     * @inheritdoc
     */
    public function getVariants($except = [])
    {
        return array_map(
            '\basename',
            FileHelper::findFiles($this->getPath(), ['except' => ArrayHelper::merge([$this->originalFileName], $except)])
        );
    }

    /**
     * @inheritdoc
     */
    public function set(UploadedFile $file, $variant = null): ?string
    {
        if (is_uploaded_file($file->tempName)) {
            $destination = $this->get($variant);
            move_uploaded_file($file->tempName, $destination);
            @chmod($destination, $this->fileMode);
            $metadata = $this->file->metadata;
            $metadata->{Metadata::WELL_KNOWN_METADATA_UPLOAD_MIMETYPE} = $file->type;
            $metadata->{Metadata::WELL_KNOWN_METADATA_UPLOAD_HASH} = sha1_file($destination);
            $metadata->{Metadata::WELL_KNOWN_METADATA_UPLOAD_SIZE} = $file->size;

            return $destination;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function setContent($content, $variant = null): ?string
    {
        $destination = $this->get($variant);
        file_put_contents($destination, $content);
        @chmod($destination, $this->fileMode);
        return $destination;
    }

    /**
     * @inheritdoc
     */
    public function setByPath(string $path, $variant = null): ?string
    {
        $destination = $this->get($variant);
        copy($path, $destination);
        @chmod($destination, $this->fileMode);
        return $destination;
    }


    /**
     * @inheritdoc
     */
    public function delete($variant = null, $except = [], $options = [])
    {
        $path = $this->getPath();

        if ($variant === null) {
            $options ??= [];
            $options['except'] = $except;
            foreach (FileHelper::findFiles($path, $options) as $f) {
                if (is_file($f)) {
                    FileHelper::unlink($f);
                }
            }
        } elseif (is_file($this->get($variant))) {
            FileHelper::unlink($this->get($variant));
        }

        while (FileHelper::isDirEmpty($path) === true) {
            rmdir($path);
            $path = dirname($path);
        }
    }

    /**
     * @inheritdoc
     */
    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Returns the path where the files of this file are located
     *
     * @return string the path
     * @throws InvalidFileGuidException when the File::$guid property is empty
     * @throws Exception when the directory for the file could not be created
     */
    protected function getPath(): string
    {
        if (empty($this->file->guid)) {
            throw new InvalidFileGuidException('File GUID empty!');
        }

        $basePath = Yii::getAlias($this->storagePath);

        // File storage prior HumHub 1.2
        if (is_dir($basePath . DIRECTORY_SEPARATOR . $this->file->guid)) {
            return $basePath . DIRECTORY_SEPARATOR . $this->file->guid;
        }

        $path = $basePath . DIRECTORY_SEPARATOR .
            $this->file->guid[0] . DIRECTORY_SEPARATOR .
            $this->file->guid[1] . DIRECTORY_SEPARATOR .
            $this->file->guid;

        FileHelper::createDirectory($path, $this->dirMode, true);

        return $path;
    }
}
