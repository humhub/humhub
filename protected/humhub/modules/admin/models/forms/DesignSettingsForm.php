<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\components\Theme;
use humhub\helpers\ThemeHelper;
use humhub\libs\LogoImage;
use humhub\modules\file\validators\ImageSquareValidator;
use humhub\modules\stream\actions\Stream;
use humhub\modules\user\helpers\LoginBackgroundImageHelper;
use humhub\modules\user\models\ProfileField;
use humhub\modules\web\pwa\widgets\SiteIcon;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * DesignSettingsForm
 *
 * @since 0.5
 */
class DesignSettingsForm extends Model
{
    public $theme;
    public $paginationSize;
    public $displayNameFormat;
    public $displayNameSubFormat;
    public $spaceOrder;
    public $logo;
    public $icon;
    public $loginBackgroundImage;
    public $dateInputDisplayFormat;
    public $defaultStreamSort;
    public $themePrimaryColor;
    public $useDefaultThemePrimaryColor;
    public $themeSecondaryColor;
    public $useDefaultThemeSecondaryColor;
    public $themeSuccessColor;
    public $useDefaultThemeSuccessColor;
    public $themeDangerColor;
    public $useDefaultThemeDangerColor;
    public $themeWarningColor;
    public $useDefaultThemeWarningColor;
    public $themeInfoColor;
    public $useDefaultThemeInfoColor;
    public $themeLightColor;
    public $useDefaultThemeLightColor;
    public $themeDarkColor;
    public $useDefaultThemeDarkColor;
    public $themeCustomScss;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $settingsManager = Yii::$app->settings;
        $themeVariables = Yii::$app->view->theme->variables;
        $themeVariables->flushCache();

        $this->theme = Yii::$app->view->theme->name;
        $this->paginationSize = $settingsManager->get('paginationSize');
        $this->displayNameFormat = $settingsManager->get('displayNameFormat');
        $this->displayNameSubFormat = $settingsManager->get('displayNameSubFormat');
        $this->spaceOrder = Yii::$app->getModule('space')->settings->get('spaceOrder');
        $this->dateInputDisplayFormat = Yii::$app->getModule('admin')->settings->get('defaultDateInputFormat');
        $this->defaultStreamSort = Yii::$app->getModule('stream')->settings->get('defaultSort');

        $this->themePrimaryColor = $settingsManager->get('themePrimaryColor', $themeVariables->get('primary'));
        $this->useDefaultThemePrimaryColor = (bool)$settingsManager->get('useDefaultThemePrimaryColor', true);
        $this->themeSecondaryColor = $settingsManager->get('themeSecondaryColor', $themeVariables->get('secondary'));
        $this->useDefaultThemeSecondaryColor = (bool)$settingsManager->get('useDefaultThemeSecondaryColor', true);
        $this->themeSuccessColor = $settingsManager->get('themeSuccessColor', $themeVariables->get('success'));
        $this->useDefaultThemeSuccessColor = (bool)$settingsManager->get('useDefaultThemeSuccessColor', true);
        $this->themeDangerColor = $settingsManager->get('themeDangerColor', $themeVariables->get('danger'));
        $this->useDefaultThemeDangerColor = (bool)$settingsManager->get('useDefaultThemeDangerColor', true);
        $this->themeWarningColor = $settingsManager->get('themeWarningColor', $themeVariables->get('warning'));
        $this->useDefaultThemeWarningColor = (bool)$settingsManager->get('useDefaultThemeWarningColor', true);
        $this->themeInfoColor = $settingsManager->get('themeInfoColor', $themeVariables->get('info'));
        $this->useDefaultThemeInfoColor = (bool)$settingsManager->get('useDefaultThemeInfoColor', true);
        $this->themeLightColor = $settingsManager->get('themeLightColor', $themeVariables->get('light'));
        $this->useDefaultThemeLightColor = (bool)$settingsManager->get('useDefaultThemeLightColor', true);
        $this->themeDarkColor = $settingsManager->get('themeDarkColor', $themeVariables->get('dark'));
        $this->useDefaultThemeDarkColor = (bool)$settingsManager->get('useDefaultThemeDarkColor', true);

        $this->themeCustomScss = $settingsManager->get('themeCustomScss');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['paginationSize', 'required'],
            ['paginationSize', 'integer', 'max' => 200, 'min' => 1],
            ['theme', 'in', 'range' => $this->getThemes()],
            [['displayNameFormat', 'displayNameSubFormat', 'spaceOrder'], 'safe'],
            ['logo', 'image', 'extensions' => 'png, jpg, jpeg', 'minWidth' => 100, 'minHeight' => 120],
            [['defaultStreamSort'], 'in', 'range' => array_keys($this->getDefaultStreamSortOptions())],
            ['icon', 'image', 'extensions' => 'png, jpg, jpeg', 'minWidth' => 256, 'minHeight' => 256],
            ['icon', ImageSquareValidator::class],
            ['loginBackgroundImage', 'image', 'extensions' => 'png, jpg, jpeg', 'minWidth' => 800, 'minHeight' => 600],
            ['dateInputDisplayFormat', 'in', 'range' => ['', 'php:d/m/Y']],
            [['themePrimaryColor', 'themeSecondaryColor', 'themeSuccessColor', 'themeDangerColor', 'themeWarningColor', 'themeInfoColor', 'themeLightColor', 'themeDarkColor', 'themeCustomScss'], 'string'],
            [['useDefaultThemePrimaryColor', 'useDefaultThemeSecondaryColor', 'useDefaultThemeSuccessColor', 'useDefaultThemeDangerColor', 'useDefaultThemeWarningColor', 'useDefaultThemeInfoColor', 'useDefaultThemeLightColor', 'useDefaultThemeDarkColor'], 'boolean'],
            [['themePrimaryColor', 'themeSecondaryColor', 'themeSuccessColor', 'themeDangerColor', 'themeWarningColor', 'themeInfoColor', 'themeLightColor', 'themeDarkColor', 'themeCustomScss'], 'trim'],
            [['themePrimaryColor', 'themeSecondaryColor', 'themeSuccessColor', 'themeDangerColor', 'themeWarningColor', 'themeInfoColor', 'themeLightColor', 'themeDarkColor'], 'match', 'pattern' => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            ['themeCustomScss', 'filter', 'filter' => function ($value) {
                $patterns = [
                    '/<style>/',
                    '/<style type="text\/css">/',
                    '/<\/style>/',
                ];
                $replacements = ['', '', ''];
                return preg_replace($patterns, $replacements, $value);
            }],
            ['themeCustomScss', function ($attribute, $params, $validator) {
                $compiler = new Compiler();
                try {
                    $compiler->compileString($this->$attribute)->getCss();
                } catch (SassException $e) {
                    $this->addError($attribute, Yii::t('AdminModule.settings', 'Cannot compile SCSS to CSS:') . ' ' . $e->getMessage());
                }
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'theme' => Yii::t('AdminModule.settings', 'Theme'),
            'paginationSize' => Yii::t('AdminModule.settings', 'Default pagination size (Entries per page)'),
            'displayNameFormat' => Yii::t('AdminModule.settings', 'User Display Name'),
            'displayNameSubFormat' => Yii::t('AdminModule.settings', 'User Display Name Subtitle'),
            'spaceOrder' => Yii::t('AdminModule.settings', '"My Spaces" Sorting'),
            'logo' => Yii::t('AdminModule.settings', 'Logo upload'),
            'icon' => Yii::t('AdminModule.settings', 'Icon upload'),
            'loginBackgroundImage' => Yii::t('AdminModule.settings', 'Login Background'),
            'dateInputDisplayFormat' => Yii::t('AdminModule.settings', 'Date input format'),
            'themePrimaryColor' => Yii::t('AdminModule.settings', 'Primary color'),
            'useDefaultThemePrimaryColor' => Yii::t('AdminModule.settings', 'Default'),
            'themeSecondaryColor' => Yii::t('AdminModule.settings', 'Secondary color'),
            'useDefaultThemeSecondaryColor' => Yii::t('AdminModule.settings', 'Default'),
            'themeSuccessColor' => Yii::t('AdminModule.settings', 'Success color'),
            'useDefaultThemeSuccessColor' => Yii::t('AdminModule.settings', 'Default'),
            'themeDangerColor' => Yii::t('AdminModule.settings', 'Danger color'),
            'useDefaultThemeDangerColor' => Yii::t('AdminModule.settings', 'Default'),
            'themeWarningColor' => Yii::t('AdminModule.settings', 'Warning color'),
            'useDefaultThemeWarningColor' => Yii::t('AdminModule.settings', 'Default'),
            'themeInfoColor' => Yii::t('AdminModule.settings', 'Info color'),
            'useDefaultThemeInfoColor' => Yii::t('AdminModule.settings', 'Default'),
            'themeLightColor' => Yii::t('AdminModule.settings', 'Light color'),
            'useDefaultThemeLightColor' => Yii::t('AdminModule.settings', 'Default'),
            'themeDarkColor' => Yii::t('AdminModule.settings', 'Dark color'),
            'useDefaultThemeDarkColor' => Yii::t('AdminModule.settings', 'Default'),
            'themeCustomScss' => Yii::t('AdminModule.settings', 'Custom SCSS'),
        ];
    }

    /**
     * @inerhitdoc
     */
    public function attributeHints()
    {
        return [
            'spaceOrder' => Yii::t('AdminModule.settings', 'Custom sort order can be defined in the Space advanced settings.'),
            'themeCustomScss' => Yii::t('AdminModule.settings', 'Use Sassy CSS syntax (SCSS)'),
        ];
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

        $files = UploadedFile::getInstancesByName('icon');
        if (count($files) != 0) {
            $file = $files[0];
            $this->icon = $file;
        }

        $files = UploadedFile::getInstancesByName('loginBackgroundImage');
        if (count($files) != 0) {
            $file = $files[0];
            $this->loginBackgroundImage = $file;
        }

        return parent::load($data, $formName);
    }

    /**
     * @return array a list of available themes
     */
    public function getThemes()
    {
        $themes = [];

        foreach (ThemeHelper::getThemes() as $theme) {
            $themes[$theme->name] = $theme->name;
        }

        return $themes;
    }

    /**
     * Saves the form
     *
     * @return bool
     */
    public function save()
    {
        $settingsManager = Yii::$app->settings;

        $theme = ThemeHelper::getThemeByName($this->theme);
        if ($theme !== null) {
            $theme->activate();
            Yii::$app->view->theme = new Theme($theme); // Force new theme immediately, e.g. to rebuild the CSS files
        }

        $settingsManager->set('paginationSize', $this->paginationSize);
        $settingsManager->set('displayNameFormat', $this->displayNameFormat);
        $settingsManager->set('displayNameSubFormat', $this->displayNameSubFormat);
        Yii::$app->getModule('space')->settings->set('spaceOrder', $this->spaceOrder);
        Yii::$app->getModule('admin')->settings->set('defaultDateInputFormat', $this->dateInputDisplayFormat);

        Yii::$app->getModule('stream')->settings->set('defaultSort', $this->defaultStreamSort);

        if ($this->logo) {
            LogoImage::set($this->logo);
        }

        if ($this->icon) {
            SiteIcon::set($this->icon);
        }

        if ($this->loginBackgroundImage && $this->loginBackgroundImage instanceof UploadedFile) {
            LoginBackgroundImageHelper::set($this->loginBackgroundImage->tempName);
        }

        $settingsManager->set('themePrimaryColor', $this->useDefaultThemePrimaryColor ? null : $this->themePrimaryColor);
        $settingsManager->set('useDefaultThemePrimaryColor', $this->useDefaultThemePrimaryColor);
        $settingsManager->set('themeSecondaryColor', $this->useDefaultThemeSecondaryColor ? null : $this->themeSecondaryColor);
        $settingsManager->set('useDefaultThemeSecondaryColor', $this->useDefaultThemeSecondaryColor);
        $settingsManager->set('themeSuccessColor', $this->useDefaultThemeSuccessColor ? null : $this->themeSuccessColor);
        $settingsManager->set('useDefaultThemeSuccessColor', $this->useDefaultThemeSuccessColor);
        $settingsManager->set('themeDangerColor', $this->useDefaultThemeDangerColor ? null : $this->themeDangerColor);
        $settingsManager->set('useDefaultThemeDangerColor', $this->useDefaultThemeDangerColor);
        $settingsManager->set('themeWarningColor', $this->useDefaultThemeWarningColor ? null : $this->themeWarningColor);
        $settingsManager->set('useDefaultThemeWarningColor', $this->useDefaultThemeWarningColor);
        $settingsManager->set('themeInfoColor', $this->useDefaultThemeInfoColor ? null : $this->themeInfoColor);
        $settingsManager->set('useDefaultThemeInfoColor', $this->useDefaultThemeInfoColor);
        $settingsManager->set('themeLightColor', $this->useDefaultThemeLightColor ? null : $this->themeLightColor);
        $settingsManager->set('useDefaultThemeLightColor', $this->useDefaultThemeLightColor);
        $settingsManager->set('themeDarkColor', $this->useDefaultThemeDarkColor ? null : $this->themeDarkColor);
        $settingsManager->set('useDefaultThemeDarkColor', $this->useDefaultThemeDarkColor);

        $settingsManager->set('themeCustomScss', $this->themeCustomScss);

        return true;
    }

    /**
     * Returns available options for defaultStreamSort attribute
     * @return array
     */
    public function getDefaultStreamSortOptions()
    {
        return [
            Stream::SORT_CREATED_AT => Yii::t('AdminModule.settings', 'Sort by creation date'),
            Stream::SORT_UPDATED_AT => Yii::t('AdminModule.settings', 'Sort by update date'),
        ];
    }

    /**
     * Returns a list of possible subtitle attribute names
     *
     * @return array
     */
    public function getDisplayNameSubAttributes()
    {
        $availableAttributes = [];
        foreach (ProfileField::find()->all() as $profileField) {
            $availableAttributes[$profileField->internal_name] = $profileField->title;
        }
        return $availableAttributes;
    }
}
