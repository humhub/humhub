<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\components\Appearance;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Administration → Appearance → Dark Mode
 *
 * @since 1.20
 */
class DarkModeSettingsForm extends Model
{
    public $allowDark;
    public $defaultColorMode;
    public $logoDark;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->allowDark = Yii::$app->appearance->isDarkModeAllowed();
        $this->defaultColorMode = Yii::$app->appearance->getDefault(Appearance::COLOR_MODE);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['allowDark', 'boolean'],
            ['defaultColorMode', 'in', 'range' => array_keys($this->getDefaultColorModes())],
            [
                'logoDark',
                'image',
                'extensions' => 'png, jpg, jpeg',
                'minWidth' => AppearanceImagesForm::LOGO_MIN_WIDTH,
                'minHeight' => AppearanceImagesForm::LOGO_MIN_WIDTH,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'allowDark' => Yii::t('AdminModule.settings', 'Allow dark mode'),
            'defaultColorMode' => Yii::t('AdminModule.settings', 'Default colour mode'),
            'logoDark' => Yii::t('AdminModule.settings', 'Dark logo'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'allowDark' => Yii::t('AdminModule.settings', 'Users can choose the colour mode in their account settings.'),
            'defaultColorMode' => Yii::t('AdminModule.settings', 'For guests and for users who did not choose a colour mode.'),
            'logoDark' => Yii::t('AdminModule.settings', 'Optional, replaces the logo in the dark mode. Same size as the logo.'),
        ];
    }

    public function getDefaultColorModes(): array
    {
        return [
            Appearance::LIGHT => Yii::t('AdminModule.settings', 'Light'),
            Appearance::SYSTEM => Yii::t('AdminModule.settings', 'System setting'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        $files = UploadedFile::getInstancesByName('logoDark');
        if ($files !== []) {
            $this->logoDark = $files[0];
        }

        return parent::load($data, $formName);
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        Yii::$app->getModule('admin')->settings->set(Appearance::SETTING_ALLOW_DARK, $this->allowDark ? 1 : null);
        Yii::$app->appearance->setDefault(Appearance::COLOR_MODE, $this->defaultColorMode);

        if ($this->logoDark instanceof UploadedFile) {
            Yii::$app->img->logoDark->setUploadedFile($this->logoDark);
        }

        return true;
    }
}
