<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file;

use humhub\modules\file\handler\UploadAudioFileHandler;
use humhub\modules\file\handler\UploadImageFileHandler;
use humhub\modules\file\handler\UploadVideoFileHandler;
use humhub\modules\file\media\FfprobeReader;
use humhub\modules\file\media\GetId3Reader;
use humhub\modules\file\media\MediaInfoReaderInterface;
use yii\di\Instance;

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
        'image/jpeg',
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
     * @since 1.20
     * @var int The maximum width in px of the display version of an image, which the media viewer shows instead of a
     * larger original (`DisplayImage`).
     */
    public $imageDisplayMaxWidth = 2048;

    /**
     * @since 1.20
     * @var int The maximum height in px of the display version of an image (`DisplayImage`).
     */
    public $imageDisplayMaxHeight = 2048;

    /**
     * @since 1.10
     * @var int The maximum megapixels(width*height) of processing image files.
     */
    public $imageMaxProcessingMP = 64;

    /**
     * @since 1.15
     * @var string[] List of file types added to the upload dropdown button (must be classes extending the BaseFileHandler class)
     */
    public $defaultFileHandlers = [UploadAudioFileHandler::class, UploadImageFileHandler::class, UploadVideoFileHandler::class];

    /**
     * @var string|null Path of the ffprobe binary. When set, the metadata of video and audio files is read with
     * ffprobe, otherwise with getID3.
     * @since 1.20
     */
    public ?string $ffprobePath = null;

    /**
     * @var MediaInfoReaderInterface|array|string|null Reader for the metadata of video and audio files (an object, a
     * configuration or a class name), instead of the one [[$ffprobePath]] selects
     * @since 1.20
     */
    public $mediaInfoReader = null;

    /**
     * @var string[] Variants that go with the file, by exact name or, when the pattern ends in `*`, by
     * prefix (`subtitles-*`; a `*` elsewhere is no wildcard): they are kept when a new content is stored and by
     * `php yii file/delete-variants`. Modules may add patterns for their own variants.
     * @since 1.20
     */
    public array $persistentVariants = ['old-*', 'subtitles-*'];

    /**
     * Whether a record's attachment list leaves out the media files its preview above shows (setting
     * `excludeMediaFilesPreview`, on unless an admin switched it off)
     *
     * @since 1.20
     */
    public function getExcludeMediaFilesPreview(): bool
    {
        return (bool)$this->settings->get('excludeMediaFilesPreview', 1);
    }

    /**
     * The reader for the metadata of video and audio files
     *
     * @since 1.20
     */
    public function getMediaInfoReader(): MediaInfoReaderInterface
    {
        if ($this->mediaInfoReader !== null) {
            return $this->mediaInfoReader = Instance::ensure($this->mediaInfoReader, MediaInfoReaderInterface::class);
        }

        return $this->ffprobePath ? new FfprobeReader($this->ffprobePath) : new GetId3Reader();
    }
}
