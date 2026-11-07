<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\models\forms;

use humhub\helpers\EmojiHelper;
use humhub\helpers\ThemeHelper;
use Yii;
use yii\base\Model;

/**
 * Administration → Appearance → Theme: the theme, its colours and the emoji style
 *
 * Each colour is the setting `theme{Color}Color`, used while `useDefaultTheme{Color}Color` is off.
 *
 * @since 1.20
 */
class ThemeSettingsForm extends Model
{
    public const COLORS = ['primary', 'accent', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];

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
    public $emojiStyle;

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

        $this->emojiStyle = EmojiHelper::getStyle();
    }

    public static function colorAttribute(string $color): string
    {
        return 'theme' . ucfirst($color) . 'Color';
    }

    public static function defaultAttribute(string $color): string
    {
        return 'useDefaultTheme' . ucfirst($color) . 'Color';
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
            ['emojiStyle', 'in', 'range' => array_keys(EmojiHelper::getStyles())],
        ];
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
            'emojiStyle' => Yii::t('AdminModule.settings', 'Emoji style'),
        ] + array_fill_keys(
            array_map(static::defaultAttribute(...), static::COLORS),
            Yii::t('AdminModule.settings', 'Default'),
        );
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

        $theme = ThemeHelper::getThemeByName($this->theme);
        if ($theme !== null) {
            $theme->activate();
            // Force new theme immediately, e.g. to rebuild the CSS files
            Yii::$app->view->theme = $theme;
        }

        foreach (static::COLORS as $color) {
            $attribute = static::colorAttribute($color);
            $default = static::defaultAttribute($color);
            Yii::$app->settings->set($attribute, $this->$default ? null : $this->$attribute);
            Yii::$app->settings->set($default, $this->$default);
        }

        Yii::$app->settings->set(
            EmojiHelper::SETTING_STYLE,
            $this->emojiStyle === EmojiHelper::STYLE_NOTO ? null : $this->emojiStyle,
        );

        return true;
    }
}
