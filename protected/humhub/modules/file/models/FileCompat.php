<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

use Exception;
use humhub\components\ActiveRecord;
use humhub\libs\MimeHelper;
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\file\libs\FileHelper;

/**
 * FileCompat provides an compatibility layer for older HumHub Version (1.1 and prior).
 *
 * @since 1.2
 * @author Luke
 */
class FileCompat extends ActiveRecord
{
    /**
     * Returns all files belongs to a given HActiveRecord Object.
     *
     * @param ActiveRecord $object
     * @return array of File instances
     * @deprecated since version 1.2
     */
    public static function getFilesOfObject(ActiveRecord $object)
    {
        return $object->fileManager->findAll();
    }

    /**
     * Returns the path of stored file
     *
     * @param string $suffix
     * @return string path to file
     * @deprecated since version 1.2
     */
    public function getStoredFilePath($suffix = '')
    {
        return $this->store->get($suffix);
    }

    /**
     * Return the preview image url of the file
     *
     * @param int $maxWidth
     * @param int $maxHeight
     * @return string
     * @deprecated since version 1.2
     */
    public function getPreviewImageUrl($maxWidth = 1000, $maxHeight = 1000)
    {
        $previewImage = new PreviewImage();
        $previewImage->applyFile($this);
        return $previewImage->getUrl();
    }

    /**
     * Attaches a given list of files to an record (HActiveRecord).
     * This is used when uploading files before the record is created yet.
     *
     * @param \yii\db\ActiveRecord $object is a HActiveRecord
     * @param string $files is a comma seperated list of newly uploaded file guids
     * @throws Exception
     * @deprecated since version 1.2
     */
    public static function attachPrecreated($object, $files)
    {
        if (!$object instanceof ActiveRecord) {
            throw new Exception('Invalid object given - require instance of \humhub\components\ActiveRecord!');
        }
        $object->fileManager->attach($files);
    }

    /**
     * Returns the filename
     *
     * @param string $suffix
     * @return string
     * @deprecated since version 1.2
     */
    public function getFilename($suffix = "")
    {
        // without prefix
        if ($suffix == "") {
            return $this->file_name;
        }

        $fileParts = pathinfo($this->file_name);

        return $fileParts['filename'] . "_" . $suffix . "." . $fileParts['extension'];
    }

    /**
     * Returns an array with informations about the file
     *
     * @return type
     * @deprecated since version 1.2
     */
    public function getInfoArray()
    {
        $info = [];

        $info['error'] = false;
        $info['guid'] = $this->guid;
        $info['name'] = $this->file_name;
        $info['title'] = $this->title;
        $info['size'] = $this->size;
        $info['mimeIcon'] = MimeHelper::getMimeIconClassByExtension($this->getExtension());
        $info['mimeBaseType'] = $this->getMimeBaseType();
        $info['mimeSubType'] = $this->getMimeSubType();
        $info['url'] = $this->getUrl("", false);

        $previewImage = new PreviewImage();
        $previewImage->applyFile($this);
        $info['thumbnailUrl'] = $previewImage->getUrl();

        return $info;
    }

    /**
     * @return string
     * @deprecated since version 1.2
     */
    public function getMimeBaseType()
    {
        if ($this->mime_type != "") {
            list($baseType, $subType) = explode('/', $this->mime_type);
            return $baseType;
        }

        return "";
    }

    /**
     * @return string
     * @deprecated since version 1.2
     */
    public function getMimeSubType()
    {
        if ($this->mime_type != "") {
            list($baseType, $subType) = explode('/', $this->mime_type);
            return $subType;
        }

        return "";
    }

    /**
     * Returns the extension of the file_name
     *
     * @return string the extension
     * @deprecated since version 1.2
     */
    public function getExtension()
    {
        return FileHelper::getExtension($this->file_name);
    }

}
