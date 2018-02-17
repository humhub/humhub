<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
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

    /** IconClass */
    const MIME_WORD = 'mime-word';
    const MIME_EXCEL = 'mime-excel';
    const MIME_POWERPOINT = 'mime-powerpoint';
    const MIME_PDF = 'mime-pdf';
    const MIME_ZIP = 'mime-zip';
    const MIME_IMAGE = 'mime-image';
    const MIME_AUDIO = 'mime-audio';
    const MIME_VIDEO = 'mime-video';
    const MIME_PHOTOSHOP = 'mime-photoshop';
    const MIME_ILLUSTRATOR = 'mime-illustrator';
    const MIME_FILE = 'mime-file';

    /** @var array Map for Extension to IconClass */
    private static $extensionToIconClass = [
        // Word
        'doc' => self::MIME_WORD,
        'docx' => self::MIME_WORD,
        'odt' => self::MIME_WORD,
        // Excel
        'xls' => self::MIME_EXCEL,
        'xlsx' => self::MIME_EXCEL,
        'ods' => self::MIME_EXCEL,
        // Powerpoint
        'ppt' => self::MIME_POWERPOINT,
        'pptx' => self::MIME_POWERPOINT,
        'pps' => self::MIME_POWERPOINT,
        'ppsx' => self::MIME_POWERPOINT,
        'odp' => self::MIME_POWERPOINT,
        // PDF
        'pdf' => self::MIME_PDF,
        // Archive
        'zip' => self::MIME_ZIP,
        'gzip' => self::MIME_ZIP,
        'rar' => self::MIME_ZIP,
        'tar' => self::MIME_ZIP,
        '7z' => self::MIME_ZIP,
        // Image
        'jpg' => self::MIME_IMAGE,
        'jpeg' => self::MIME_IMAGE,
        'png' => self::MIME_IMAGE,
        'gif' => self::MIME_IMAGE,
        'webp' => self::MIME_IMAGE,
        'tiff' => self::MIME_IMAGE,
        // Audio
        'mp3' => self::MIME_AUDIO,
        'aiff' => self::MIME_AUDIO,
        'wav' => self::MIME_AUDIO,
        'ogg' => self::MIME_AUDIO,
        // Video
        'avi' => self::MIME_VIDEO,
        'mp4' => self::MIME_VIDEO,
        'mov' => self::MIME_VIDEO,
        'mpeg' => self::MIME_VIDEO,
        'wma' => self::MIME_VIDEO,
        'webm' => self::MIME_VIDEO,
        'mkv' => self::MIME_VIDEO,
        // Adobe Photoshop
        'psd' => self::MIME_PHOTOSHOP,
        // Adobe Illustrator
        'ai' => self::MIME_ILLUSTRATOR
    ];

    /**
     * Returns Stylesheet Classname based on file extension
     *
     * @param string|File $ext The file extension or file object
     * @return string the CSS Class
     */
    public static function getMimeIconClassByExtension($ext)
    {
        if ($ext instanceof File) {
            $ext = FileHelper::getExtension($ext);
        }

        // lowercase string
        $ext = strtolower($ext);

        if (isset(self::$extensionToIconClass[$ext])) {
            return self::$extensionToIconClass[$ext];
        }

        return self::MIME_FILE;
    }
}
