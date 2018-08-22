<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use humhub\libs\LogoImage;
use humhub\libs\DynamicConfig;
use humhub\modules\ui\view\helpers\ThemeHelper;

/**
 * DesignSettingsForm
 *
 * @since 0.5
 */
class DesignSettingsForm extends Model
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

        $this->theme = Yii::$app->view->theme->name;
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
        return [
            ['paginationSize', 'integer', 'max' => 200, 'min' => 1],
            ['theme', 'in', 'range' => $this->getThemes()],
            [['displayName', 'spaceOrder'], 'safe'],
            [['horImageScrollOnMobile'], 'boolean'],
            ['logo', 'file', 'extensions' => ['jpg', 'png', 'jpeg'], 'maxSize' => Yii::$app->getModule('file')->settings->get('maxFileSize')],
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
            if ($height < 40) {
                $this->addError('logo', 'Logo size should have at least 40px of height');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        $files = UploadedFile::getInstancesByName('logo');
        if (count($files) != 0) {
            $file = $files[0];
            $this->logo = $file;
        }

        return parent::load($data, $formName);
    }

    /**
     * @return array a list of available themes
     */
    public function getThemes() {
        $themes = [];

        foreach (ThemeHelper::getThemes() as $theme) {
            $themes[$theme->name] = $theme->name;
        }

        return $themes;
    }

    /**
     * Saves the form
     *
     * @return boolean
     */
    public function save()
    {
        $settingsManager = Yii::$app->settings;

        $theme = ThemeHelper::getThemeByName($this->theme);
        if ($theme !== null) {
            $theme->activate();
        }

        $settingsManager->set('paginationSize', $this->paginationSize);
        $settingsManager->set('displayNameFormat', $this->displayName);
        Yii::$app->getModule('space')->settings->set('spaceOrder', $this->spaceOrder);
        Yii::$app->getModule('admin')->settings->set('defaultDateInputFormat', $this->dateInputDisplayFormat);
        $settingsManager->set('horImageScrollOnMobile', $this->horImageScrollOnMobile);

        if ($this->logo) {
            $logoImage = new LogoImage();
            $logoImage->setNew($this->logo);
        }

        DynamicConfig::rewrite();

        return true;
    }

}
