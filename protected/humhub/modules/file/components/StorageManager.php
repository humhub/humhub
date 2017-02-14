<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\components;

use Yii;
use humhub\modules\file\models\File;
use humhub\modules\file\libs\ImageConverter;
use humhub\modules\file\libs\FileHelper;

/**
 * StorageManager for File records
 *
 * @since 1.2
 * @author Luke
 */
class StorageManager extends \yii\base\Component implements StorageManagerInterface
{

    /**
     * @var string file name of the base file (without variant)
     */
    public $originalFileName = 'file';

    /**
     * @var string storage base path
     */
    protected $storagePath = '@webroot/uploads/file';

    /**
     * @var integer file mode 
     */
    public $fileMode = 0744;

    /**
     * @var File
     */
    protected $file;

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
    public function getVariants()
    {
        $variants = [];
        foreach (scandir($this->getPath()) as $file) {
            if (!in_array($file, [$this->originalFileName, '.', '..'])) {
                $variants[] = $file;
            }
        }
        return $variants;
    }

    /**
     * @inheritdoc
     */
    public function set(\yii\web\UploadedFile $file, $variant = null)
    {
        if (is_uploaded_file($file->tempName)) {
            move_uploaded_file($file->tempName, $this->get($variant));
            @chmod($this->get($variant), $this->fileMode);
        }

        /**
         * For uploaded jpeg files convert them again - to handle special
         * exif attributes (e.g. orientation)
         */
        if ($file->type == 'image/jpeg') {
            ImageConverter::TransformToJpeg($this->get($variant), $this->get($variant));
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
    public function delete($variant = null)
    {
        if ($variant === null) {
            $path = $this->getPath();

            // Make really sure, that we dont delete something else :-)
            if ($this->file->guid != "" && is_dir($path)) {
                $files = glob($path . DIRECTORY_SEPARATOR . "*");
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                rmdir($path);
            }
        } elseif (is_file($this->get($variant))) {
            unlink($this->get($variant));
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
            throw new \Exception('File GUID empty!');
        }

        $basePath = Yii::getAlias($this->storagePath);

        // File storage prior HumHub 1.2
        if (is_dir($basePath . DIRECTORY_SEPARATOR . $this->file->guid)) {
            return $basePath . DIRECTORY_SEPARATOR . $this->file->guid;
        }

        $path = $basePath . DIRECTORY_SEPARATOR .
                substr($this->file->guid, 0, 1) . DIRECTORY_SEPARATOR .
                substr($this->file->guid, 1, 1) . DIRECTORY_SEPARATOR .
                $this->file->guid;

        if (!is_dir($path)) {
            FileHelper::createDirectory($path, $this->fileMode, true);
        }

        return $path;
    }

}
