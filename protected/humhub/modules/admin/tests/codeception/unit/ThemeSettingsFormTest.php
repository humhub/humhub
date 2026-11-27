<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\tests\codeception\unit;

use humhub\components\Theme;
use humhub\helpers\ThemeHelper;
use humhub\modules\admin\models\forms\ThemeSettingsForm;
use humhub\services\ActiveThemeService;
use ReflectionProperty;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\FileHelper;

class ThemeSettingsFormTest extends HumHubDbTestCase
{
    /**
     * A second theme on top of the core theme with a primary colour of its own, as Enterprise has
     */
    private const OTHER_THEME = 'ThemeSettingsFormTestTheme';
    private const OTHER_THEME_PRIMARY = '#2d3340';

    private ?string $otherThemePath = null;

    protected function _after()
    {
        foreach (ThemeSettingsForm::COLORS as $color) {
            Yii::$app->settings->delete(ThemeSettingsForm::colorAttribute($color));
        }

        if ($this->otherThemePath !== null) {
            $coreTheme = ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME);
            $coreTheme->activate();
            Yii::$app->view->theme = $coreTheme;
            FileHelper::removeDirectory($this->otherThemePath);
            (new ReflectionProperty(ThemeHelper::class, '_themes'))->setValue(null, null);
        }
        ActiveThemeService::flush();

        parent::_after();
    }

    public function testSettingNameOfAHyphenatedColor()
    {
        $this->assertSame('themePrimaryColor', ThemeSettingsForm::colorAttribute('primary'));
        $this->assertSame('themeBodyBgDarkColor', ThemeSettingsForm::colorAttribute('body-bg-dark'));
    }

    public function testShowsTheThemesColorsAndTheStoredDeviations()
    {
        Yii::$app->settings->set('themeAccentColor', '#1700c7');

        $model = new ThemeSettingsForm();

        $this->assertSame('#435f6f', $model->themePrimaryColor);
        $this->assertSame('#1700c7', $model->themeAccentColor);
    }

    /**
     * "Reset colors" is shown while a colour of the form differs from the theme's own
     */
    public function testHasCustomColors()
    {
        $model = new ThemeSettingsForm();
        $this->assertFalse($model->hasCustomColors());

        $model->themeBodyBgColor = '#FFFFFF'; // The core theme's `#fff`, written differently
        $this->assertFalse($model->hasCustomColors());

        $model->themeAccentColor = '#1700c7';
        $this->assertTrue($model->hasCustomColors());

        Yii::$app->settings->set('themeDangerColor', '#b00020');
        $this->assertTrue((new ThemeSettingsForm())->hasCustomColors());
    }

    public function testSurfaceColorKeepsTheTextReadable()
    {
        $model = new ThemeSettingsForm();

        $model->themeBodyBgColor = '#fdf8ec';
        $model->themeBodyBgDarkColor = '#1b2a44';
        $this->assertTrue($model->validate(['themeBodyBgColor', 'themeBodyBgDarkColor']));

        // The light mode's grey text on a dark surface, the dark mode's light grey text on a light one
        $model->themeBodyBgColor = '#555555';
        $model->themeBodyBgDarkColor = '#cccccc';
        $this->assertFalse($model->validate(['themeBodyBgColor', 'themeBodyBgDarkColor']));
        $this->assertTrue($model->hasErrors('themeBodyBgColor'));
        $this->assertTrue($model->hasErrors('themeBodyBgDarkColor'));
    }

    /**
     * A colour equal to the theme's own is not stored, so it keeps following the theme; a deviation is stored as
     * lowercase `#rrggbb`
     */
    public function testStoresTheDeviationsFromTheThemeOnly()
    {
        $model = new ThemeSettingsForm();
        $model->themePrimaryColor = '#435F6F'; // The core theme's primary, written differently
        $model->themeAccentColor = '#ABC';

        $this->assertTrue($model->save());

        $this->assertNull(Yii::$app->settings->get('themePrimaryColor'));
        $this->assertSame('#aabbcc', Yii::$app->settings->get('themeAccentColor'));
        $this->assertNull(Yii::$app->settings->get('themeDangerColor'));
    }

    /**
     * The colours of the form are those of the active theme. On a switch to another theme, the ones left as they were
     * take the new theme's own: a theme with the colours of the core theme switched to Enterprise looks like Enterprise
     */
    public function testUntouchedColorsFollowTheThemeSwitchedTo()
    {
        $this->addOtherTheme();

        $this->assertTrue($this->submit(['theme' => static::OTHER_THEME]));

        $this->assertSame(static::OTHER_THEME, Yii::$app->view->theme->name);
        $this->assertNoColorStored();
        $this->assertSame(static::OTHER_THEME_PRIMARY, Yii::$app->view->theme->variable('primary'));

        // And back again
        $this->assertTrue($this->submit(['theme' => Theme::CORE_THEME_NAME]));

        $this->assertSame(Theme::CORE_THEME_NAME, Yii::$app->view->theme->name);
        $this->assertNoColorStored();
        $this->assertSame('#435f6f', Yii::$app->view->theme->variable('primary'));
    }

    /**
     * The theme's own colour as the colour picker writes it (`#ffffff` for the core theme's `#fff`) is untouched too
     */
    public function testAnUntouchedColorInAnotherNotationFollowsTheThemeSwitchedTo()
    {
        $this->addOtherTheme();

        $this->assertTrue($this->submit(['theme' => static::OTHER_THEME, 'themePrimaryColor' => '#435F6F', 'themeBodyBgColor' => '#ffffff']));

        $this->assertNoColorStored();
    }

    public function testAChangedColorStaysOnTheThemeSwitchedTo()
    {
        $this->addOtherTheme();
        Yii::$app->settings->set('themeAccentColor', '#1700c7');

        $this->assertTrue($this->submit(['theme' => static::OTHER_THEME, 'themeDangerColor' => '#b00020']));

        $this->assertSame('#1700c7', Yii::$app->settings->get('themeAccentColor'));
        $this->assertSame('#b00020', Yii::$app->settings->get('themeDangerColor'));
        $this->assertNull(Yii::$app->settings->get('themePrimaryColor'));
    }

    /**
     * Submits the form as rendered for the active theme, with the given changes
     */
    private function submit(array $changes): bool
    {
        $model = new ThemeSettingsForm();
        $values = $model->getAttributes();

        return $model->load([$model->formName() => array_merge($values, $changes)]) && $model->save();
    }

    private function assertNoColorStored(): void
    {
        foreach (ThemeSettingsForm::COLORS as $color) {
            $this->assertNull(Yii::$app->settings->get(ThemeSettingsForm::colorAttribute($color)), $color);
        }
    }

    private function addOtherTheme(): void
    {
        $this->otherThemePath = Yii::getAlias('@runtime') . '/tests/themes/' . static::OTHER_THEME;
        FileHelper::removeDirectory($this->otherThemePath);
        FileHelper::createDirectory($this->otherThemePath . '/scss');
        FileHelper::createDirectory($this->otherThemePath . '/resources');
        file_put_contents(
            $this->otherThemePath . '/scss/variables.scss',
            '$baseTheme: "HumHub";' . "\n" . '$primary: ' . static::OTHER_THEME_PRIMARY . ' !default;' . "\n",
        );

        $themes = ThemeHelper::getThemes();
        $themes[static::OTHER_THEME] = ThemeHelper::getThemeByPath($this->otherThemePath);
        (new ReflectionProperty(ThemeHelper::class, '_themes'))->setValue(null, $themes);
    }
}
