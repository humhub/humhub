<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\modules\file\models\forms;

use humhub\modules\file\libs\FileControllerInterface;
use humhub\modules\file\libs\ImageControllerInterface;
use humhub\modules\file\models\File;
use humhub\modules\file\validators\FileValidator;
use Yii;
use yii\web\UploadedFile;

/**
 * FileUpload model is used for File uploads handled by the UploadAction via ajax.
 *
 * @see    \humhub\modules\file\actions\UploadAction
 * @since  1.15
 */
trait FileUploadTrait
{
    /**
     * @var UploadedFile|null the uploaded file
     */
    protected ?UploadedFile $uploadedFile = null;

    public function __get($name)
    {
        if ($name === static::$fileUploadFieldName) {
            return $this->uploadedFile;
        }

        return parent::__get($name);
    }

    public function __set(
        $name,
        $value
    ) {
        if ($name === static::$fileUploadFieldName) {
            $this->setUploadedFile($value);

            return;
        }

        parent::__set($name, $value);
    }

    protected function getFieldNameFromController(): ?string
    {
        if (($controller = Yii::$app->controller) && $controller instanceof FileControllerInterface) {
            return $controller->getActionConfiguration(FileControllerInterface::ACTION_UPLOAD)->fileListParameterName;
        }

        return null;
    }

    /**
     * @return UploadedFile|null
     */
    public function getUploadedFile(): ?UploadedFile
    {
        return $this->uploadedFile;
    }

    /**
     * Sets uploaded file to this file model
     *
     * @param UploadedFile $uploadedFile
     */
    public function setUploadedFile(UploadedFile $uploadedFile)
    {
        // Set Filename
        $filename  = $uploadedFile->getBaseName();
        $extension = $uploadedFile->getExtension();
        if ($extension !== '') {
            $filename .= '.' . $extension;
        }

        $this->file_name    = File::sanitizeFilename($filename, $countPattern, $countExtension) ?? $filename;
        $this->mime_type    = $uploadedFile->type;
        $this->size         = $uploadedFile->size;
        $this->uploadedFile = $uploadedFile;

        if ($countPattern || $countExtension) {
            $this->metadata = ['uploaded.filename' => $filename];
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave(
        $insert,
        $changedAttributes
    ) {
        // Store file
        if ($this->uploadedFile instanceof UploadedFile) {
            $this->setStoredFile($this->uploadedFile);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $maxSize = Yii::$app->getModule('file')->settings->get('maxFileSize');

        $rules   = parent::rules();
        $rules[] = [['uploadedFile'], 'required'];
        $rules[] = array_merge(
            [
                ['uploadedFile'],
                'file',
            ],
            $this->fileValidatorArguments,
            [
                'extensions' => $this->fileUploadAllowedExtensions,
                'maxSize'    => $maxSize,
            ]
        );

        return $rules;
    }
}
