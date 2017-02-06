<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\libs;

/**
 * FileHelper
 *
 * @since 1.2
 * @author Luke
 */
class FileHelper extends \yii\helpers\FileHelper
{

    /**
     * Checks if given fileName has a extension
     * 
     * @param string $fileName
     * @return boolean
     */
    public static function hasExtension($fileName)
    {
        return (strpos($fileName, ".") !== false);
    }

    /**
     * Returns the extension of a file
     * 
     * @param string $fileName
     * @return string the extension
     */
    public static function getExtension($fileName)
    {
        $fileParts = pathinfo($fileName);
        if (isset($fileParts['extension'])) {
            return $fileParts['extension'];
        }
        return '';
    }

}
