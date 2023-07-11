<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\validators;

use humhub\exceptions\InvalidArgumentTypeException;
use humhub\modules\file\Module;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\UploadedFile;
use humhub\modules\file\models\File;
use humhub\modules\file\libs\FileHelper;

/**
 * FileNameValidatorTrait
 *
 * @since 1.15
 */
trait FileNameValidatorTrait
{
    /**
     * @var array|string|null a list of file name extensions that are allowed to be uploaded.
     * This can be either an array or a string consisting of file extension names
     * separated by space or comma (e.g. "gif, jpg").
     * Extension names are case-insensitive. Defaults to null, meaning all file name
     * extensions are allowed.
     * @see wrongExtension for the customized message for wrong file type.
     */
    public $extensions;

    /**
     * @var bool whether to check file type (extension) with mime-type. If extension produced by
     * file mime-type check differs from uploaded file extension, the file will be considered as invalid.
     */
    public $checkExtensionByMimeType = true;

    /**
     * @var int|null the maximum number of bytes required for the uploaded file.
     * Defaults to null, meaning no limit.
     * Note, the size limit is also affected by `upload_max_filesize` and `post_max_size` INI setting
     * and the 'MAX_FILE_SIZE' hidden field value. See [[getSizeLimit()]] for details.
     * @see https://www.php.net/manual/en/ini.core.php#ini.upload-max-filesize
     * @see https://www.php.net/post-max-size
     * @see getSizeLimit
     * @see tooBig for the customized message for a file that is too big.
     */
    public $maxSize;

    /**
     * @var boolean allow only file extensions which are specified in administration section
     */
    public bool $useDefaultExtensionRestriction = true;

    /**
     * @var boolean deny double file extensions
     */
    public ?bool $denyDoubleFileExtensions = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('file');

        if ($this->extensions === null && $this->useDefaultExtensionRestriction) {
            $this->extensions = $module->settings->get('allowedExtensions');
        }

        if ($this->maxSize === null) {
            $this->maxSize = $module->settings->get('maxFileSize');
        }

        if ($this->denyDoubleFileExtensions === null) {
            $this->denyDoubleFileExtensions = $module->denyDoubleFileExtensions;
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($file): ?array
    {
        return parent::validateValue($file);
    }

    public function validateFileName($file)
    {
        if ($file instanceof File) {
            $sanitized = File::sanitizeFilename($file->file_name, $countPattern, $countExtension);

            if ($this->denyDoubleFileExtensions && $countExtension) {
                $this->addError($file, 'file_name', Yii::t('FileModule.base', 'Double file extensions are not allowed!'));
            }

            if ($countPattern || $countExtension) {
                $file->file_name = $sanitized;
            }
        }
    }

    /**
     * Checks if given uploaded file have correct type (extension) according current validator settings.
     * @param UploadedFile|File $file
     * @return bool
     * @throws InvalidConfigException
     */
    protected function validateExtension($file): bool
    {
        if ($file instanceof UploadedFile) {
            $filePath = $file->tempName;
            $extension = mb_strtolower($file->extension, 'UTF-8');
        } elseif ($file instanceof File) {
            $filePath = $file->store->has();
            $extension = mb_strtolower(FileHelper::getExtension($file), 'UTF-8');
        } else {
            throw new InvalidArgumentTypeException(__METHOD__, [1 => '$file'], [UploadedFile::class, File::class], $file);
        }

        if ($filePath && $this->checkExtensionByMimeType && FileHelper::getMimeTypeByExtension('test.' . $extension) !== null) {
            $mimeType = FileHelper::getMimeType($filePath, null, false);
            if ($mimeType === null) {
                $this->addError($file, 'extension', Yii::t('FileModule.base', 'Mime type cannot be determined for this file!'));
                return false;
            }

            $extensionsByMimeType = FileHelper::getExtensionsByMimeType($mimeType);

            if (!in_array($extension, $extensionsByMimeType, true)) {
                $this->addError(
                    $file,
                    'extension',
                    Yii::t('FileModule.base', 'Extension {extension} is not allowed for mime type {mime_type}!', ['extension' => $extension, 'mime_type' => $mimeType ])
                );
                return false;
            }
        }

        if ($this->extensions && !in_array($extension, (array) $this->extensions, true)) {
            $this->addError(
                $file,
                'extension',
                Yii::t('FileModule.base', 'Extension {extension} is not allowed!', ['extension' => $extension ])
            );
            return false;
        }

        return true;
    }
}
