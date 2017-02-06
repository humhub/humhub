<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\components;

/**
 * StorageManagerInterface
 * 
 * @version 1.2
 * @author Luke
 */
interface StorageManagerInterface
{

    /**
     * Returns the complete file path to the stored file (variant).
     * 
     * @param string $variant optional the variant string
     * @return string the complete file path
     */
    public function get($variant = null);

    /**
     * Adds or overwrites the file by given UploadedFile in store
     * 
     * @param \yii\web\UploadedFile $file
     * @param string $variant the variant identifier
     */
    public function set(\yii\web\UploadedFile $file, $variant = null);

    /**
     * Adds or overwrites the file content by given string in store
     * 
     * @param string $content the new file data
     * @param string $variant the variant identifier
     */
    public function setContent($content, $variant = null);

    /**
     * Deletes a stored file (-variant)
     * 
     * If not variant is given, also all file variants will be deleted
     */
    public function delete($variant = null);

    /**
     * Get file variants
     * 
     * @return array Returns the stored variants of the file
     */
    public function getVariants();

    /**
     * Sets the file for this storage manager instance
     * 
     * @param \humhub\modules\file\models\File $file
     */
    public function setFile(\humhub\modules\file\models\File $file);
}
