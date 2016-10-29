<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\models;

/**
 * FileCompat provides an compatiblity layer for older HumHub Version (1.1 and prior).
 *
 * @since 1.2
 * @author Luke
 */
class FileCompat extends \humhub\components\ActiveRecord
{

    /**
     * Returns all files belongs to a given HActiveRecord Object.
     *
     * @deprecated since version 1.2
     * @param \humhub\components\ActiveRecord $object
     * @return array of File instances
     */
    public static function getFilesOfObject(\humhub\components\ActiveRecord $object)
    {
        return $object->fileManager->getAll();
    }

    /**
     * Returns the path of stored file
     * 
     * @deprecated since version 1.2
     * @param string $suffix
     * @return string path to file
     */
    public function getStoredFilePath($suffix = '')
    {
        return $this->store->get($suffix);
    }

    /**
     * Return the preview image url of the file
     * 
     * @deprecated since version 1.2
     * @param int $maxWidth
     * @param int $maxHeight
     * @return string
     */
    public function getPreviewImageUrl($maxWidth = 1000, $maxHeight = 1000)
    {
        $previewImage = new \humhub\modules\file\converter\PreviewImage();
        $previewImage->applyFile($this);
        return $previewImage->getUrl();
    }

    /**
     * Attaches a given list of files to an record (HActiveRecord).
     * This is used when uploading files before the record is created yet.
     *
     * @deprecated since version 1.2
     * @param \yii\db\ActiveRecord $object is a HActiveRecord
     * @param string $files is a comma seperated list of newly uploaded file guids
     */
    public static function attachPrecreated($object, $files)
    {
        if (!$object instanceof \humhub\components\ActiveRecord) {
            throw new Exception('Invalid object given - require instance of \humhub\components\ActiveRecord!');
        }
        $object->fileManager->attach($files);
    }

}
