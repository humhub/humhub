<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\tests\codeception\unit;

use humhub\modules\admin\models\forms\ThemeSettingsForm;
use humhub\services\ActiveThemeService;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ThemeSettingsFormTest extends HumHubDbTestCase
{
    protected function _after()
    {
        foreach (ThemeSettingsForm::COLORS as $color) {
            Yii::$app->settings->delete(ThemeSettingsForm::colorAttribute($color));
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
}
