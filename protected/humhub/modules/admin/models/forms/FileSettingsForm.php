<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * FileSettingsForm
 * @since 0.5
 */
class FileSettingsForm extends \yii\base\Model
{

    public $maxFileSize;
    public $maxPreviewImageWidth;
    public $maxPreviewImageHeight;
    public $hideImageFileInfo;
    public $useXSendfile;
    public $allowedExtensions;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->getModule('file')->settings;

        $this->maxFileSize = $settingsManager->get('maxFileSize') / 1024 / 1024;
        $this->maxPreviewImageWidth = $settingsManager->get('maxPreviewImageWidth');
        $this->maxPreviewImageHeight = $settingsManager->get('maxPreviewImageHeight');
        $this->hideImageFileInfo = $settingsManager->get('hideImageFileInfo');
        $this->useXSendfile = $settingsManager->get('useXSendfile');
        $this->allowedExtensions = $settingsManager->get('allowedExtensions');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['allowedExtensions'], 'match', 'pattern' => '/^[A-Za-z0-9_,]+$/u'],
            [['maxFileSize', 'useXSendfile', 'maxPreviewImageWidth', 'maxPreviewImageHeight', 'hideImageFileInfo'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'maxFileSize' => Yii::t('AdminModule.settings', 'Maximum upload file size (in MB)'),
            'useXSendfile' => Yii::t('AdminModule.settings', 'Use X-Sendfile for File Downloads'),
            'maxPreviewImageWidth' => Yii::t('AdminModule.settings', 'Maximum preview image width (in pixels, optional)'),
            'maxPreviewImageHeight' => Yii::t('AdminModule.settings', 'Maximum preview image height (in pixels, optional)'),
            'hideImageFileInfo' => Yii::t('AdminModule.settings', 'Hide file info (name, size) for images on wall'),
            'allowedExtensions' => Yii::t('AdminModule.settings', 'Allowed file extensions'),
        ];
    }


    /**
     * Saves the form
     *
     * @return boolean
     */
    public function save()
    {
        $settingsManager = Yii::$app->getModule('file')->settings;
        $settingsManager->set('maxFileSize', $this->maxFileSize * 1024 * 1024);
        $settingsManager->set('maxPreviewImageWidth', $this->maxPreviewImageWidth);
        $settingsManager->set('maxPreviewImageHeight', $this->maxPreviewImageHeight);
        $settingsManager->set('hideImageFileInfo', $this->hideImageFileInfo);
        $settingsManager->set('useXSendfile', $this->useXSendfile);
        $settingsManager->set('allowedExtensions', strtolower($this->allowedExtensions));

        return true;
    }

}
