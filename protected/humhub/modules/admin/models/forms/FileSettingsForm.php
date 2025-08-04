<?php

namespace humhub\modules\admin\models\forms;

use humhub\libs\Helpers;
use Yii;
use yii\base\Model;

/**
 * FileSettingsForm
 * @property int $maxFileSize
 * @property int $excludeMediaFilesPreview Exclude media files from stream attachment list
 * @property int $useXSendfile
 * @property string $allowedExtensions
 *
 * @since 0.5
 */
class FileSettingsForm extends Model
{
    public $maxFileSize;
    public $excludeMediaFilesPreview;
    public $useXSendfile;
    public $allowedExtensions;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        [,,$defaultValue] = self::getPHPMaxUploadSize();

        $settingsManager = Yii::$app->getModule('file')->settings;

        $this->maxFileSize = ($settingsManager->get('maxFileSize') / 1024 / 1024) ?: $defaultValue;
        $this->excludeMediaFilesPreview = $settingsManager->get('excludeMediaFilesPreview');
        $this->useXSendfile = $settingsManager->get('useXSendfile');
        $this->allowedExtensions = $settingsManager->get('allowedExtensions');
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        [$a,$maxUploadSize, $defaultValue] = self::getPHPMaxUploadSize();

        return [
            [['allowedExtensions'], 'match', 'pattern' => '/^[A-Za-z0-9_,]+$/u'],
            [['useXSendfile', 'excludeMediaFilesPreview'], 'integer'],
            [['maxFileSize'], 'default', 'value' => $defaultValue],
            [['maxFileSize'], 'integer', 'min' => 1, 'max' => $maxUploadSize],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'maxFileSize' => Yii::t('AdminModule.settings', 'Maximum upload file size (in MB)'),
            'useXSendfile' => Yii::t('AdminModule.settings', 'Use X-Sendfile for File Downloads'),
            'excludeMediaFilesPreview' => Yii::t('AdminModule.settings', 'Exclude media files from stream attachment list'),
            'allowedExtensions' => Yii::t('AdminModule.settings', 'Allowed file extensions'),
        ];
    }

    public function attributeHints(): array
    {
        [$fileSizeKey, $maxUploadSize] = self::getPHPMaxUploadSize();

        return [
            'maxFileSize' => Yii::t('AdminModule.settings', 'PHP reported a maximum of {maxUploadSize} MB', [
                '{maxUploadSize}' => "(" . $fileSizeKey . "): " . $maxUploadSize,
            ]),
            'allowedExtensions' => Yii::t('AdminModule.settings', 'Comma separated list. Leave empty to allow all.'),
        ];
    }

    public static function getPHPMaxUploadSize(): array
    {
        $maxUploadSize = Helpers::getBytesOfIniValue(ini_get('upload_max_filesize'));
        $fileSizeKey = 'upload_max_filesize';
        if ($maxUploadSize > Helpers::getBytesOfIniValue(ini_get('post_max_size'))) {
            $maxUploadSize = Helpers::getBytesOfIniValue(ini_get('post_max_size'));
            $fileSizeKey = 'post_max_size';
        }

        $maxUploadSizeInMb = $maxUploadSize / 1024 / 1024;

        return [
            $fileSizeKey,
            $maxUploadSizeInMb,
            min($maxUploadSizeInMb, 64),
        ];
    }


    /**
     * Saves the form
     *
     * @return bool
     */
    public function save(): bool
    {
        $settingsManager = Yii::$app->getModule('file')->settings;
        $settingsManager->set('maxFileSize', (int)$this->maxFileSize * 1024 * 1024);
        $settingsManager->set('excludeMediaFilesPreview', $this->excludeMediaFilesPreview);
        $settingsManager->set('useXSendfile', $this->useXSendfile);
        $settingsManager->set('allowedExtensions', strtolower($this->allowedExtensions));

        return true;
    }

}
