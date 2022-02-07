<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file;

/**
 * File Module
 *
 * @since 0.5
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $isCoreModule = true;

    /**
     * @var string The characters will be replaced in file names with underscore '_' on uploading
     */
    public $fileNameValidationPattern = '/[\x00-\x1F\x80-\xA0>\/\<":\*?|{}\[\]\\\\\/]/u';

    /**
     * @see components\StorageManagerInterface
     * @var string storage manager class for files
     */
    public $storageManagerClass = '\humhub\modules\file\components\StorageManager';

    /**
     * @var array mime types to show inline instead of download
     */
    public $inlineMimeTypes = [
        'application/pdf',
        'application/x-pdf',
        'image/gif',
        'image/png',
        'image/jpeg'
    ];

    /**
     * @var array Additional MIME types which are not detected correctly by function finfo_file()
     */
    public $additionalMimeTypes = [
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    /**
     * @var bool Prohibit uploads of files with double file extension.
     */
    public $denyDoubleFileExtensions = false;

    /**
     * @var array of converter options
     */
    public $converterOptions = [];

    /**
     * @since 1.7
     * @var string maximum image resolution before downscaling e.g. 1920x1080
     */
    public $imageMaxResolution = null;

    /**
     * @since 1.7
     * @var int The JPEG quality for uploaded JPEG images. From 0 to 100.
     */
    public $imageJpegQuality = null;

    /**
     * @since 1.7
     * @var int The PNG compression level for uploaded PNG images. From 0 to 9.
     */
    public $imagePngCompressionLevel = null;

    /**
     * @since 1.7
     * @var int The WebP quality for uploaded WebP files. From 0 to 100.
     */
    public $imageWebpQuality = null;

    /**
     * @since 1.7
     * @var int The maximum height of generated preview image files in px.
     */
    public $imagePreviewMaxHeight = 400;

    /**
     * @since 1.7
     * @var int The maximum width of generated preview image files in px.
     */
    public $imagePreviewMaxWidth = 400;

    /**
     * @since 1.10
     * @var int The maximum megapixels(width*height) of processing image files.
     */
    public $imageMaxProcessingMP = 64;
}
