<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class FileSettingsForm extends CFormModel {

    public $imageMagickPath;
    public $maxFileSize;
    public $useXSendfile;
    public $forbiddenExtensions;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
            array('imageMagickPath', 'checkImageMagick'),
            array('maxFileSize, useXSendfile', 'numerical', 'integerOnly' => true),
            array('imageMagickPath, maxFileSize', 'safe'),
            array('forbiddenExtensions', 'safe'),

        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'imageMagickPath' => Yii::t('AdminModule.base', 'Image Magick convert command (optional)'),
            'maxFileSize' => Yii::t('AdminModule.base', 'Maximum upload file size (in MB)'),
            'useXSendfile' => Yii::t('AdminModule.base', 'Use X-Sendfile for File Downloads'),
            'forbiddenExtensions' =>  Yii::t('AdminModule.base', 'Forbidden file extensions'),
        );
    }

    public function checkImageMagick($attribute, $params) {
        if ($this->$attribute != "") {
            $this->$attribute = trim($this->$attribute);
            
            if (is_file($this->$attribute)) {

                exec($this->$attribute . " --help", $returnIM);

                if (strpos(implode("\n", $returnIM), "ImageMagick") === false) {
                    $this->addError($attribute, Yii::t('AdminModule.base', "Got invalid image magick response! - Correct command?"));
                }
            } else {
                $this->addError($attribute, Yii::t('AdminModule.base', "Convert command not found!"));
            }


        }
    }

}