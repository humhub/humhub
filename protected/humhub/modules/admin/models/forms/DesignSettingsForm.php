<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use Yii;
use humhub\libs\ThemeHelper;

/**
 * DesignSettingsForm
 *
 * @since 0.5
 */
class DesignSettingsForm extends \yii\base\Model
{

    public $theme;
    public $paginationSize;
    public $displayName;
    public $spaceOrder;
    public $logo;
    public $dateInputDisplayFormat;
    public $horImageScrollOnMobile;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->settings;

        $this->theme = $settingsManager->get('theme');
        $this->paginationSize = $settingsManager->get('paginationSize');
        $this->displayName = $settingsManager->get('displayNameFormat');
        $this->spaceOrder = Yii::$app->getModule('space')->settings->get('spaceOrder');
        $this->dateInputDisplayFormat = Yii::$app->getModule('admin')->settings->get('defaultDateInputFormat');
        $this->horImageScrollOnMobile = $settingsManager->get('horImageScrollOnMobile');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $themes = [];
        foreach (ThemeHelper::getThemes() as $theme) {
            $themes[] = $theme->name;
        }

        return [
            ['paginationSize', 'integer', 'max' => 200, 'min' => 1],
            ['theme', 'in', 'range' => $themes],
            [['displayName', 'spaceOrder'], 'safe'],
            [['horImageScrollOnMobile'], 'boolean'],
            ['logo', 'file', 'extensions' => ['jpg', 'png', 'jpeg'], 'maxSize' => 3 * 1024 * 1024],
            ['logo', 'dimensionValidation', 'skipOnError' => true],
            ['dateInputDisplayFormat', 'in', 'range' => ['', 'php:d/m/Y']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'theme' => Yii::t('AdminModule.forms_DesignSettingsForm', 'Theme'),
            'paginationSize' => Yii::t('AdminModule.forms_DesignSettingsForm', 'Default pagination size (Entries per page)'),
            'displayName' => Yii::t('AdminModule.forms_DesignSettingsForm', 'Display Name (Format)'),
            'spaceOrder' => Yii::t('AdminModule.forms_DesignSettingsForm', 'Dropdown space order'),
            'logo' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Logo upload'),
            'dateInputDisplayFormat' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Date input format'),
            'horImageScrollOnMobile' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Horizontal scrolling images on a mobile device'),
        ];
    }

    /**
     * Dimension Validator
     */
    public function dimensionValidation($attribute, $param)
    {
        if (is_object($this->logo)) {
            list($width, $height) = getimagesize($this->logo->tempName);
            if ($height < 40)
                $this->addError('logo', 'Logo size should have at least 40px of height');
        }
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        $files = \yii\web\UploadedFile::getInstancesByName('logo');
        if (count($files) != 0) {
            $file = $files[0];
            $this->logo = $file;
        }

        return parent::load($data, $formName);
    }

    /**
     * Saves the form
     *
     * @return boolean
     */
    public function save()
    {
        $settingsManager = Yii::$app->settings;

        $settingsManager->set('theme', $this->theme);
        \humhub\components\Theme::setColorVariables($this->theme);
        $settingsManager->set('paginationSize', $this->paginationSize);
        $settingsManager->set('displayNameFormat', $this->displayName);
        Yii::$app->getModule('space')->settings->set('spaceOrder', $this->spaceOrder);
        Yii::$app->getModule('admin')->settings->set('defaultDateInputFormat', $this->dateInputDisplayFormat);
        $settingsManager->set('horImageScrollOnMobile', $this->horImageScrollOnMobile);

        if ($this->logo) {
            $logoImage = new \humhub\libs\LogoImage();
            $logoImage->setNew($this->logo);
        }

        \humhub\libs\DynamicConfig::rewrite();

        return true;
    }

}
