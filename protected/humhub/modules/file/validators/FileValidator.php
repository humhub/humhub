<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\validators;

use humhub\modules\file\Module;
use Yii;
use yii\web\UploadedFile;
use humhub\modules\file\models\File;
use humhub\modules\file\libs\FileHelper;

/**
 * FileValidator
 *
 * @inheritdoc
 * @since 1.2
 * @author Luke
 */
class FileValidator extends \yii\validators\FileValidator
{

    /**
     * @var boolean allow only file extensions which are specified in administration section
     */
    public $useDefaultExtensionRestriction = true;

    /**
     * @var boolean deny double file extensions
     */
    public $denyDoubleFileExtensions;

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
    protected function validateValue($file)
    {
        $errors = parent::validateValue($file);
        if ($errors !== null) {
            return $errors;
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $this->validateFileName($model, $attribute);
        parent::validateAttribute($model, $attribute);
    }

    public function validateFileName($model, $attribute)
    {
        if ($model instanceof File) {
            $pattern = Yii::$app->moduleManager->getModule('file')->fileNameValidationPattern;

            if (empty($pattern)) {
                return;
            }

            $model->file_name = preg_replace($pattern, '_', $model->file_name);

            if($this->denyDoubleFileExtensions && preg_match('/\.\w{2,3}\.\w{2,3}$/', $model->file_name)) {
                $this->addError($model, $attribute, Yii::t('FileModule.base', 'Double file extensions are not allowed!'));
            }
        }
    }


    /**
     * Checks if given uploaded file have correct type (extension) according current validator settings.
     * @param UploadedFile $file
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function validateExtension($file)
    {
        $extension = mb_strtolower($file->extension, 'UTF-8');

        if (FileHelper::getMimeTypeByExtension('test.' . $extension) !== null && $this->checkExtensionByMimeType) {
            $mimeType = FileHelper::getMimeType($file->tempName, null, false);
            if ($mimeType === null) {
                return false;
            }

            $extensionsByMimeType = FileHelper::getExtensionsByMimeType($mimeType);

            if (!in_array($extension, $extensionsByMimeType, true)) {
                return false;
            }
        }

        if (!in_array($extension, $this->extensions, true)) {
            return false;
        }

        return true;
    }
}
