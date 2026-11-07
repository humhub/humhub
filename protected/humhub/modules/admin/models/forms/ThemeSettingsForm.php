<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\helpers\ScssHelper;
use humhub\helpers\ThemeHelper;
use Yii;
use yii\base\Model;
use yii\helpers\Inflector;

/**
 * Administration → Appearance → Theme: the theme and its colours
 *
 * Each colour is the Sass variable of its name, stored in the setting `theme{Color}Color` (e.g. `themeBodyBgDarkColor`
 * for `body-bg-dark`) and used while `useDefaultTheme{Color}Color` is off.
 *
 * @since 1.20
 */
class ThemeSettingsForm extends Model
{
    public const COLORS = [
        'primary', 'accent', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'body-bg', 'body-bg-dark',
    ];

    /**
     * The surface colours and the text colour that has to stay readable on them, with its value of the core theme
     */
    public const SURFACE_TEXT_COLORS = ['body-bg' => ['body-color', '#555555'], 'body-bg-dark' => ['body-color-dark', '#dddddd']];

    /**
     * The contrast the text needs on a surface colour (WCAG 2.2 AA for normal text)
     */
    public const MIN_SURFACE_CONTRAST = 4.5;

    public $theme;
    public $themePrimaryColor;
    public $useDefaultThemePrimaryColor;
    public $themeAccentColor;
    public $useDefaultThemeAccentColor;
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
    public $themeBodyBgColor;
    public $useDefaultThemeBodyBgColor;
    public $themeBodyBgDarkColor;
    public $useDefaultThemeBodyBgDarkColor;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->theme = Yii::$app->view->theme->name;

        $themeVariables = Yii::$app->view->theme->variables;
        foreach (static::COLORS as $color) {
            $attribute = static::colorAttribute($color);
            $default = static::defaultAttribute($color);
            $this->$attribute = Yii::$app->settings->get($attribute, $themeVariables->get($color));
            $this->$default = (bool)Yii::$app->settings->get($default, true);
        }
    }

    public static function colorAttribute(string $color): string
    {
        return 'theme' . Inflector::id2camel($color) . 'Color';
    }

    public static function defaultAttribute(string $color): string
    {
        return 'useDefaultTheme' . Inflector::id2camel($color) . 'Color';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $colorAttributes = array_map(static::colorAttribute(...), static::COLORS);

        return [
            ['theme', 'in', 'range' => $this->getThemes()],
            [$colorAttributes, 'trim'],
            [$colorAttributes, 'match', 'pattern' => '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            [array_map(static::defaultAttribute(...), static::COLORS), 'boolean'],
            [array_map(static::colorAttribute(...), array_keys(static::SURFACE_TEXT_COLORS)), 'validateSurfaceContrast'],
        ];
    }

    /**
     * The text of the theme has to stay readable on a surface colour: a light one in the light mode, a dark one in the
     * dark mode
     */
    public function validateSurfaceContrast(string $attribute): void
    {
        $surfaces = array_keys(static::SURFACE_TEXT_COLORS);
        $surface = array_combine(array_map(static::colorAttribute(...), $surfaces), $surfaces)[$attribute];
        if ($this->{static::defaultAttribute($surface)} || $this->hasErrors($attribute)) {
            return;
        }

        [$textVariable, $textDefault] = static::SURFACE_TEXT_COLORS[$surface];
        $textColor = (string)Yii::$app->view->theme->variable($textVariable);
        if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $textColor)) {
            $textColor = $textDefault;
        }

        $ratio = ScssHelper::getContrastRatio($textColor, $this->$attribute);
        if ($ratio < static::MIN_SURFACE_CONTRAST) {
            $this->addError($attribute, Yii::t('AdminModule.settings', 'The text is not readable on this color (contrast {ratio}:1, at least {min}:1 is needed). Choose a {direction} color.', [
                'ratio' => round($ratio, 1),
                'min' => static::MIN_SURFACE_CONTRAST,
                'direction' => ScssHelper::getRelativeLuminance($textColor) < 0.5
                    ? Yii::t('AdminModule.settings', 'lighter')
                    : Yii::t('AdminModule.settings', 'darker'),
            ]));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'theme' => Yii::t('AdminModule.settings', 'Theme'),
            'themePrimaryColor' => Yii::t('AdminModule.settings', 'Primary color'),
            'themeAccentColor' => Yii::t('AdminModule.settings', 'Accent color'),
            'themeSecondaryColor' => Yii::t('AdminModule.settings', 'Secondary color'),
            'themeSuccessColor' => Yii::t('AdminModule.settings', 'Success color'),
            'themeDangerColor' => Yii::t('AdminModule.settings', 'Danger color'),
            'themeWarningColor' => Yii::t('AdminModule.settings', 'Warning color'),
            'themeInfoColor' => Yii::t('AdminModule.settings', 'Info color'),
            'themeLightColor' => Yii::t('AdminModule.settings', 'Light color'),
            'themeDarkColor' => Yii::t('AdminModule.settings', 'Dark color'),
            'themeBodyBgColor' => Yii::t('AdminModule.settings', 'Surface color'),
            'themeBodyBgDarkColor' => Yii::t('AdminModule.settings', 'Surface color in the dark mode'),
        ] + array_fill_keys(
            array_map(static::defaultAttribute(...), static::COLORS),
            Yii::t('AdminModule.settings', 'Default'),
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'themeBodyBgColor' => Yii::t('AdminModule.settings', 'Cards, panels and dialogs. The page behind them, the lines and the other areas are derived from it.'),
            'themeBodyBgDarkColor' => Yii::t('AdminModule.settings', 'The same in the dark mode. Keep it dark.'),
        ];
    }

    /**
     * @return array a list of available themes
     */
    public function getThemes(): array
    {
        $themes = [];
        foreach (ThemeHelper::getThemes() as $theme) {
            $themes[$theme->name] = $theme->name;
        }

        return $themes;
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        // Before the activation: it rebuilds the theme's variables, which are derived from these colours
        foreach (static::COLORS as $color) {
            $attribute = static::colorAttribute($color);
            $default = static::defaultAttribute($color);
            Yii::$app->settings->set($attribute, $this->$default ? null : $this->$attribute);
            Yii::$app->settings->set($default, $this->$default);
        }

        $theme = ThemeHelper::getThemeByName($this->theme);
        if ($theme !== null) {
            $theme->activate();
            // Force new theme immediately, e.g. to rebuild the CSS files
            Yii::$app->view->theme = $theme;
        }

        return true;
    }
}
