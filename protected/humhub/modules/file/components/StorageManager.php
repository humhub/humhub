<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\components;

use Exception;
use humhub\modules\file\models\File;
use humhub\modules\file\libs\FileHelper;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\imagine\Image;
use yii\web\UploadedFile;

/**
 * StorageManager for File records
 *
 * @since 1.2
 * @author Luke
 */
class StorageManager extends Component implements StorageManagerInterface
{
    /**
     * @var string file name of the base file (without variant)
     */
    public $originalFileName = 'file';

    /**
     * @var string storage base path
     */
    protected $storagePath = '@filestore';

    /**
     * @var int file mode
     */
    public $fileMode = 0744;

    /**
     * @var File
     */
    protected $file;

    /**
     * @inheritdoc
     */
    public function has($variant = null): bool
    {
        return file_exists($this->get($variant));
    }

    /**
     * @inheritdoc
     */
    public function get($variant = null)
    {
        if ($variant === null) {
            $variant = $this->originalFileName;
        }

        return $this->getPath() . DIRECTORY_SEPARATOR . $variant;
    }

    /**
     * @inheritdoc
     */
    public function getVariants($except = [])
    {
        return array_map(
            function (string $s): string {
                return basename($s);
            },
            FileHelper::findFiles($this->getPath(), ['except' => ArrayHelper::merge(['file'], $except)]),
        );
    }

    /**
     * @inheritdoc
     */
    public function set(UploadedFile $file, $variant = null)
    {
        if (is_uploaded_file($file->tempName)) {
            move_uploaded_file($file->tempName, $this->get($variant));
            @chmod($this->get($variant), $this->fileMode);
        }
    }

    /**
     * @inheritdoc
     */
    public function setContent($content, $variant = null)
    {
        file_put_contents($this->get($variant), $content);
        @chmod($this->get($variant), $this->fileMode);
    }

    /**
     * @inheritdoc
     */
    public function setByPath(string $path, $variant = null)
    {
        copy($path, $this->get($variant));
        @chmod($this->get($variant), $this->fileMode);
    }


    /**
     * @inheritdoc
     */
    public function delete($variant = null, $except = [])
    {
        if ($variant === null) {
            foreach (FileHelper::findFiles($this->getPath(), ['except' => $except]) as $f) {
                if (is_file($f)) {
                    FileHelper::unlink($f);
                }
            }

            if (empty($except)) {
                FileHelper::removeDirectory($this->getPath());
            }

        } elseif (is_file($this->get($variant))) {
            FileHelper::unlink($this->get($variant));
        }
    }

    /**
     * @inheritdoc
     */
    public function setFile(File $file)
    {
        $this->file = $file;
    }

    /**
     * Returns the path where the files of this file are located
     *
     * @return string the path
     */
    protected function getPath()
    {
        if ($this->file->guid == '') {
            throw new Exception('File GUID empty!');
        }

        $basePath = Yii::getAlias($this->storagePath);

        // File storage prior HumHub 1.2
        if (is_dir($basePath . DIRECTORY_SEPARATOR . $this->file->guid)) {
            return $basePath . DIRECTORY_SEPARATOR . $this->file->guid;
        }

        $path = $basePath . DIRECTORY_SEPARATOR
            . substr($this->file->guid, 0, 1) . DIRECTORY_SEPARATOR
            . substr($this->file->guid, 1, 1) . DIRECTORY_SEPARATOR
            . $this->file->guid;

        FileHelper::createDirectory($path, $this->fileMode, true);

        return $path;
    }

}
