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
    public const ICON_WORD = 'mime-word';
    public const ICON_EXCEL = 'mime-excel';
    public const ICON_POWERPOINT = 'mime-powerpoint';
    public const ICON_PDF = 'mime-pdf';
    public const ICON_ZIP = 'mime-zip';
    public const ICON_IMAGE = 'mime-image';
    public const ICON_AUDIO = 'mime-audio';
    public const ICON_VIDEO = 'mime-video';
    public const ICON_PHOTOSHOP = 'mime-photoshop';
    public const ICON_ILLUSTRATOR = 'mime-illustrator';
    public const ICON_FILE = 'mime-file';

    /** @var array Map for Extension to IconClass */
    private static $extensionToIconClass = [
        // Word
        'doc' => self::ICON_WORD,
        'docx' => self::ICON_WORD,
        'docm' => self::ICON_WORD,
        'odt' => self::ICON_WORD,
        // Excel
        'xls' => self::ICON_EXCEL,
        'xlsx' => self::ICON_EXCEL,
        'xlsb' => self::ICON_EXCEL,
        'xlsm' => self::ICON_EXCEL,
        'ods' => self::ICON_EXCEL,
        // Powerpoint
        'ppt' => self::ICON_POWERPOINT,
        'pptx' => self::ICON_POWERPOINT,
        'pps' => self::ICON_POWERPOINT,
        'ppsx' => self::ICON_POWERPOINT,
        'odp' => self::ICON_POWERPOINT,
        // PDF
        'pdf' => self::ICON_PDF,
        // Archive
        'zip' => self::ICON_ZIP,
        'gzip' => self::ICON_ZIP,
        'rar' => self::ICON_ZIP,
        'tar' => self::ICON_ZIP,
        '7z' => self::ICON_ZIP,
        // Image
        'jpg' => self::ICON_IMAGE,
        'jpeg' => self::ICON_IMAGE,
        'png' => self::ICON_IMAGE,
        'gif' => self::ICON_IMAGE,
        'webp' => self::ICON_IMAGE,
        'tiff' => self::ICON_IMAGE,
        // Audio
        'mp3' => self::ICON_AUDIO,
        'aiff' => self::ICON_AUDIO,
        'wav' => self::ICON_AUDIO,
        'ogg' => self::ICON_AUDIO,
        // Video
        'avi' => self::ICON_VIDEO,
        'mp4' => self::ICON_VIDEO,
        'mov' => self::ICON_VIDEO,
        'mpeg' => self::ICON_VIDEO,
        'wma' => self::ICON_VIDEO,
        'webm' => self::ICON_VIDEO,
        'mkv' => self::ICON_VIDEO,
        // Adobe Photoshop
        'psd' => self::ICON_PHOTOSHOP,
        // Adobe Illustrator
        'ai' => self::ICON_ILLUSTRATOR,
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

        return self::$extensionToIconClass[$ext] ?? self::ICON_FILE;
    }

    /**
     * Tabler icon names by extension - a type with a glyph of its own gets it, otherwise the
     * glyph of its family, otherwise `file` (see {@see self::getIconNameByExtension()}).
     *
     * @var array<string, string[]>
     */
    private static array $iconNameExtensions = [
        'file-type-pdf' => ['pdf'],
        'file-type-doc' => ['doc', 'docx', 'docm', 'odt', 'rtf', 'pages'],
        'file-type-xls' => ['xls', 'xlsx', 'xlsb', 'xlsm', 'ods', 'numbers'],
        'file-type-csv' => ['csv', 'tsv'],
        'file-type-ppt' => ['ppt', 'pptx', 'pps', 'ppsx', 'odp', 'key'],
        'file-type-txt' => ['txt', 'log'],
        'file-type-svg' => ['svg'],
        'file-type-jpg' => ['jpg', 'jpeg'],
        'file-type-png' => ['png'],
        'file-type-bmp' => ['bmp'],
        'photo' => ['gif', 'webp', 'avif', 'tif', 'tiff', 'heic', 'ico'],
        'file-music' => ['mp3', 'wav', 'ogg', 'oga', 'm4a', 'aac', 'flac', 'wma', 'aiff'],
        'movie' => ['mp4', 'mov', 'avi', 'webm', 'mkv', 'm4v', 'wmv', 'mpeg'],
        'file-zip' => ['zip', 'gzip', 'rar', '7z', 'tar', 'gz', 'tgz', 'bz2'],
        'file-type-html' => ['html', 'htm'],
        'file-type-css' => ['css', 'scss', 'less'],
        'file-type-js' => ['js', 'mjs', 'cjs'],
        'file-type-jsx' => ['jsx'],
        'file-type-ts' => ['ts'],
        'file-type-tsx' => ['tsx'],
        'file-type-vue' => ['vue'],
        'file-type-sql' => ['sql'],
        'file-type-rs' => ['rs'],
        'file-type-php' => ['php'],
        'file-type-xml' => ['xml'],
        'file-text' => ['md', 'markdown'],
        'file-code' => ['py', 'c', 'h', 'cpp', 'cc', 'hpp', 'cs', 'json', 'yml', 'yaml'],
        'file-settings' => ['ini', 'cfg', 'conf', 'env'],
    ];

    /**
     * Returns the Tabler icon name (without the `ti-` prefix) of a file type, for a client
     * that renders `<i class="ti ti-<name>">` itself - the counterpart of
     * {@see self::getMimeIconClassByExtension()}, whose `mime-*` classes are the platform's
     * own stylesheet sprites.
     *
     * @param string|File $ext the file extension or file object
     * @return string e.g. `file-type-pdf`, `file` for an unknown extension
     * @since 1.20
     */
    public static function getIconNameByExtension($ext): string
    {
        if ($ext instanceof File) {
            $ext = FileHelper::getExtension($ext);
        }

        $ext = strtolower((string)$ext);

        foreach (self::$iconNameExtensions as $name => $extensions) {
            if (in_array($ext, $extensions, true)) {
                return $name;
            }
        }

        return 'file';
    }
}
