<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\components\Theme;
use humhub\helpers\ScssHelper;
use humhub\helpers\ThemeHelper;
use Yii;
use yii\base\Model;
use yii\helpers\Inflector;

/**
 * Administration → Appearance → Theme: the theme and its colours
 *
 * Each colour is the Sass variable of its name, stored in the setting `theme{Color}Color` (e.g. `themeBodyBgDarkColor`
 * for `body-bg-dark`) while it differs from the theme's own value. A colour equal to the theme's is not stored, so it
 * keeps following the theme.
 *
 * @since 1.20
 */
class ThemeSettingsForm extends Model
{
    public const COLORS = ['primary', 'accent', 'secondary', 'success', 'danger', 'warning', 'info', 'body-bg', 'body-bg-dark'];

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
    public $themeAccentColor;
    public $themeSecondaryColor;
    public $themeSuccessColor;
    public $themeDangerColor;
    public $themeWarningColor;
    public $themeInfoColor;
    public $themeBodyBgColor;
    public $themeBodyBgDarkColor;

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
            $this->$attribute = Yii::$app->settings->get($attribute) ?: $themeVariables->getThemeDefault($color);
        }
    }

    public static function colorAttribute(string $color): string
    {
        return 'theme' . Inflector::id2camel($color) . 'Color';
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
            [array_map(static::colorAttribute(...), array_keys(static::SURFACE_TEXT_COLORS)), 'validateSurfaceContrast'],
        ];
    }

    /**
     * The text of the theme has to stay readable on a surface colour: a light one in the light mode, a dark one in the
     * dark mode
     */
    public function validateSurfaceContrast(string $attribute): void
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        $surfaces = array_keys(static::SURFACE_TEXT_COLORS);
        $surface = array_combine(array_map(static::colorAttribute(...), $surfaces), $surfaces)[$attribute];

        // The text of the theme chosen in the form, which may not be the active one yet
        [$textVariable, $textDefault] = static::SURFACE_TEXT_COLORS[$surface];
        $textColor = (string)$this->getSelectedTheme()->variable($textVariable);
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
            'themeBodyBgColor' => Yii::t('AdminModule.settings', 'Surface color'),
            'themeBodyBgDarkColor' => Yii::t('AdminModule.settings', 'Surface color in the dark mode'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'themePrimaryColor' => Yii::t('AdminModule.settings', 'The top bar, primary buttons and the checked state of a control.'),
            'themeAccentColor' => Yii::t('AdminModule.settings', 'Links, icons and the highlight of a selected item.'),
            'themeSecondaryColor' => Yii::t('AdminModule.settings', 'The grey button and neutral badges.'),
            'themeSuccessColor' => Yii::t('AdminModule.settings', 'Confirmations and things that went well.'),
            'themeDangerColor' => Yii::t('AdminModule.settings', 'Errors, and actions that delete.'),
            'themeWarningColor' => Yii::t('AdminModule.settings', 'Things that need attention.'),
            'themeInfoColor' => Yii::t('AdminModule.settings', 'Notices and neutral highlights.'),
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

    /**
     * The theme chosen in the form, the active one while the choice is unknown
     */
    public function getSelectedTheme(): Theme
    {
        return ThemeHelper::getThemeByName((string)$this->theme) ?? Yii::$app->view->theme;
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        // A colour equal to the theme's own is not stored: it keeps following the theme. Before the activation, which
        // rebuilds the theme's variables, derived from these colours
        $theme = $this->getSelectedTheme();
        foreach (static::COLORS as $color) {
            $attribute = static::colorAttribute($color);
            $value = ScssHelper::normalizeHexColor((string)$this->$attribute);
            $default = ScssHelper::normalizeHexColor((string)$theme->variables->getThemeDefault($color));
            Yii::$app->settings->set($attribute, $value === $default ? null : $value);
        }

        $theme->activate();
        // Force new theme immediately, e.g. to rebuild the CSS files
        Yii::$app->view->theme = $theme;

        return true;
    }
}
