<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class FileSettingsForm extends CFormModel
{

    public $imageMagickPath;
    public $maxFileSize;
    public $maxPreviewImageWidth;
    public $maxPreviewImageHeight;
    public $hideImageFileInfo;
    public $useXSendfile;
    public $allowedExtensions;
    public $showFilesWidgetBlacklist;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('imageMagickPath', 'checkImageMagick'),
            array('allowedExtensions, showFilesWidgetBlacklist', 'match', 'pattern' => '/^[A-Za-z0-9_,]+$/u'),
            array('maxFileSize, useXSendfile, maxPreviewImageWidth, maxPreviewImageHeight, hideImageFileInfo', 'numerical', 'integerOnly' => true),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'imageMagickPath' => Yii::t('AdminModule.forms_FileSettingsForm', 'Image Magick convert command (optional)'),
            'maxFileSize' => Yii::t('AdminModule.forms_FileSettingsForm', 'Maximum upload file size (in MB)'),
            'useXSendfile' => Yii::t('AdminModule.forms_FileSettingsForm', 'Use X-Sendfile for File Downloads'),
            'maxPreviewImageWidth' => Yii::t('AdminModule.forms_FileSettingsForm', 'Maximum preview image width (in pixels, optional)'),
            'maxPreviewImageHeight' => Yii::t('AdminModule.forms_FileSettingsForm', 'Maximum preview image height (in pixels, optional)'),
            'hideImageFileInfo' => Yii::t('AdminModule.forms_FileSettingsForm', 'Hide file info (name, size) for images on wall'),
            'allowedExtensions' => Yii::t('AdminModule.forms_FileSettingsForm', 'Allowed file extensions'),
            'showFilesWidgetBlacklist' => Yii::t('AdminModule.forms_FileSettingsForm', 'Hide file list widget from showing files for these objects on wall.'),
        );
    }

    public function checkImageMagick($attribute, $params)
    {
        if ($this->$attribute != "") {
            $this->$attribute = trim($this->$attribute);

            if (is_file($this->$attribute)) {

                exec($this->$attribute . " --help", $returnIM);

                if (strpos(implode("\n", $returnIM), "ImageMagick") === false) {
                    $this->addError($attribute, Yii::t('AdminModule.forms_FileSettingsForm', "Got invalid image magick response! - Correct command?"));
                }
            } else {
                $this->addError($attribute, Yii::t('AdminModule.forms_FileSettingsForm', "Convert command not found!"));
            }
        }
    }

}
