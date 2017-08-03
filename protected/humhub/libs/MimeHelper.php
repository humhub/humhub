<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;

/**
 * MimeHelper
 *
 * @author luke
 */
class MimeHelper
{

    /**
     * Returns Stylesheet Classname based on file extension
     *
     * @param string|File $ext The file extension or file object
     * @return string the CSS Class
     */
    public static function getMimeIconClassByExtension($ext)
    {
        if ($ext instanceof \humhub\modules\file\models\File) {
            $ext = FileHelper::getExtension($ext->file_name);
        }

        // lowercase string
        $ext = strtolower($ext);

        // Word
        if ($ext == 'doc' || $ext == 'docx') {
            return 'mime-word';
        // Excel
        } elseif ($ext == 'xls' || $ext == 'xlsx') {
            return 'mime-excel';
        // Powerpoint
        } elseif ($ext == 'ppt' || $ext == 'pptx') {
            return 'mime-excel';
        // PDF
        } elseif ($ext == 'pdf') {
            return 'mime-pdf';
        // Archive
        } elseif ($ext == 'zip' || $ext == 'gzip' || $ext == 'rar' || $ext == 'tar' || $ext == '7z') {
            return 'mime-zip';
        // Audio
        } elseif ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif' || $ext == 'webp') {
            return 'mime-image';
        // Audio
        } elseif ($ext == 'mp3' || $ext == 'aiff' || $ext == 'wav') {
            return 'mime-audio';
        // Video
        } elseif ($ext == 'avi' || $ext == 'mp4' || $ext == 'mov' || $ext == 'mpeg' || $ext == 'wma' || $ext == 'webm') {
            return 'mime-video';
        // Adobe Photoshop
        } elseif ($ext == 'psd') {
            return 'mime-photoshop';
        // Adobe Illustrator
        } elseif ($ext == 'ai') {
            return 'mime-illustrator';
        // other file formats
        } else {
            return 'mime-file';
        }
    }
}
