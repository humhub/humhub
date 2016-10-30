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

/**
 * StorageManager for File records
 *
 * @since 1.2
 * @author Luke
 */
class StorageManager extends \yii\base\Component implements StorageManagerInterface
{

    /**
     * @var string path to store files
     */
    public $path = "@webroot/uploads/file";

    /**
     * @var string file name of the base file (without variant)
     */
    public $originalFileName = 'file';

    /**
     * @var integer file mode 
     */
    public $fileMode = 0744;

    /**
     * @var File
     */
    protected $file;

    /**
     * Returns the complete file path to the stored file (variant).
     * 
     * @param string $variant optional the variant string
     * @return string the complete file path
     */
    public function get($variant = null)
    {
        if ($variant === null) {
            $variant = $this->originalFileName;
        }

        return $this->getPath() . DIRECTORY_SEPARATOR . $variant;
    }

    /**
     * Adds or overwrites the file by given UploadedFile in store
     * 
     * @param \Zend\Validator\File\UploadFile $uploadedFile
     * @param string $variant the variant identifier
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
     * Adds or overwrites the file content by given string in store
     * 
     * @param string $content the new file data
     * @param string $variant the variant identifier
     */
    public function setContent($content, $variant = null)
    {
        file_put_contents($this->get($variant), $this->content);
        @chmod($this->get($variant), $this->fileMode);
    }

    /**
     * Deletes a stored file (-variant)
     * 
     * If not variant is given, also all file variants will be deleted
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

        $path = Yii::getAlias($this->path) . DIRECTORY_SEPARATOR . $this->file->guid;
        if (!is_dir($path)) {
            mkdir($path);
        }

        return $path;
    }

}
