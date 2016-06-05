<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * FileSettingsForm
 * @since 0.5
 */
class FileSettingsForm extends \yii\base\Model
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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->getModule('file')->settings;

        $this->imageMagickPath = $settingsManager->get('imageMagickPath');
        $this->maxFileSize = $settingsManager->get('maxFileSize') / 1024 / 1024;
        $this->maxPreviewImageWidth = $settingsManager->get('maxPreviewImageWidth');
        $this->maxPreviewImageHeight = $settingsManager->get('maxPreviewImageHeight');
        $this->hideImageFileInfo = $settingsManager->get('hideImageFileInfo');
        $this->useXSendfile = $settingsManager->get('useXSendfile');
        $this->allowedExtensions = $settingsManager->get('allowedExtensions');
        $this->showFilesWidgetBlacklist = $settingsManager->get('showFilesWidgetBlacklist');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            array('imageMagickPath', 'checkImageMagick'),
            array(['allowedExtensions', 'showFilesWidgetBlacklist'], 'match', 'pattern' => '/^[A-Za-z0-9_,]+$/u'),
            array(['maxFileSize', 'useXSendfile', 'maxPreviewImageWidth', 'maxPreviewImageHeight', 'hideImageFileInfo'], 'integer'),
        );
    }

    /**
     * @inheritdoc
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

    /**
     * Check ImageMagick Attribute
     * 
     * @param type $attribute
     * @param type $params
     */
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

    /**
     * Saves the form
     * 
     * @return boolean
     */
    public function save()
    {
        $settingsManager = Yii::$app->getModule('file')->settings;
        $settingsManager->set('imageMagickPath', $this->imageMagickPath);
        $settingsManager->set('maxFileSize', $this->maxFileSize * 1024 * 1024);
        $settingsManager->set('maxPreviewImageWidth', $this->maxPreviewImageWidth);
        $settingsManager->set('maxPreviewImageHeight', $this->maxPreviewImageHeight);
        $settingsManager->set('hideImageFileInfo', $this->hideImageFileInfo);
        $settingsManager->set('useXSendfile', $this->useXSendfile);
        $settingsManager->set('allowedExtensions', strtolower($this->allowedExtensions));
        $settingsManager->set('showFilesWidgetBlacklist', $this->showFilesWidgetBlacklist);

        return true;
    }

}
