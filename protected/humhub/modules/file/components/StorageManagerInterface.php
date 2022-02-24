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
     * Checks if the requested file or version exists.
     *
     * @param string $variant optional the variant string
     * @return boolean
     */
    public function has($variant = null);

    /**
     * Returns the complete file path to the stored file (variant).
     *
     * @param string $variant optional the variant string
     * @return string the complete file path
     */
    public function get($variant = null);

    /**
     * Adds or overwrites the file by given UploadedFile in store.
     *
     * @param \yii\web\UploadedFile $file
     * @param string $variant the variant identifier
     * @see File::setStoredFile() Use this method to set a new file.
     */
    public function set(\yii\web\UploadedFile $file, $variant = null);

    /**
     * Adds or overwrites the file content by given string in store
     *
     * @param string $content the new file data
     * @param string $variant the variant identifier
     * @see File::setStoredFileContent()  Use this method to set a new file content.
     */
    public function setContent($content, $variant = null);

    /**
     * Adds or overwrites the file content by given file path
     *
     * @param string $path the new file path
     * @param string $variant the variant identifier
     */
    public function setByPath(string $path, $variant = null);

    /**
     * Deletes a stored file (-variant)
     *
     * If not variant is given, also all file variants will be deleted
     * @param string $variant the variant identifier
     * @param string[] $except exclude following variants from deletion
     */
    public function delete($variant = null, $except = []);

    /**
     * Get file variants
     * @param string[] $except exclude following variants from deletion
     * @return array Returns the stored variants of the file
     */
    public function getVariants($except = []);

    /**
     * Sets the file for this storage manager instance
     *
     * @param \humhub\modules\file\models\File $file
     */
    public function setFile(\humhub\modules\file\models\File $file);
}
