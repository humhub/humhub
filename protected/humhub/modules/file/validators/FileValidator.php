<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\file\validators;

use Yii;
use humhub\modules\file\models\File;
use humhub\modules\file\libs\ImageConverter;

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
     * @inheritdoc
     */
    public function init()
    {
        if ($this->extensions === null && $this->useDefaultExtensionRestriction) {
            $this->extensions = Yii::$app->getModule('file')->settings->get('allowedExtensions');
        }

        if ($this->maxSize === null) {
            $this->maxSize = Yii::$app->getModule('file')->settings->get('maxFileSize');
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

        $error = $this->checkMemoryLimit($file);
        if ($error !== null) {
            return $error;
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
        if($model instanceof File) {
            $pattern = Yii::$app->moduleManager->getModule('file')->fileNameValidationPattern;

            if(empty($pattern)) {
                return;
            }

            if(preg_match($pattern, $model->file_name)) {
                $this->addError($model, $attribute, Yii::t('FileModule.models_File', 'Invalid file name detected!'));
            }

            if(preg_match('/\.\w{2,3}\.\w{2,3}$/', $model->file_name)) {
                $this->addError($model, $attribute, Yii::t('FileModule.models_File', 'Double file extensions are not allowed!'));
            }
        }
    }

    /**
     * Checks memory limit if GD is used for image conversions
     *
     * @param \yii\web\UploadedFile $file
     * @return array|null
     * @throws \yii\base\Exception
     */
    protected function checkMemoryLimit($file)
    {
        if (Yii::$app->getModule('file')->settings->get('imageMagickPath')) {
            return null;
        }

        $convertableFileTypes = [image_type_to_mime_type(IMAGETYPE_PNG), image_type_to_mime_type(IMAGETYPE_GIF), image_type_to_mime_type(IMAGETYPE_JPEG)];
        if (in_array($file->type, $convertableFileTypes)) {
            if (!ImageConverter::allocateMemory($file->tempName, true)) {
                return [Yii::t('FileModule.models_File', 'Image dimensions are too big to be processed with current server memory limit!'), []];
            }
        }

        return null;
    }

}
