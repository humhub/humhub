<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\tests\codeception\unit;

use humhub\modules\admin\models\forms\ThemeSettingsForm;
use tests\codeception\_support\HumHubDbTestCase;

class ThemeSettingsFormTest extends HumHubDbTestCase
{
    public function testSettingNameOfAHyphenatedColor()
    {
        $this->assertSame('themePrimaryColor', ThemeSettingsForm::colorAttribute('primary'));
        $this->assertSame('themeBodyBgDarkColor', ThemeSettingsForm::colorAttribute('body-bg-dark'));
        $this->assertSame('useDefaultThemeBodyBgColor', ThemeSettingsForm::defaultAttribute('body-bg'));
    }

    public function testSurfaceColorKeepsTheTextReadable()
    {
        $model = new ThemeSettingsForm();
        $model->useDefaultThemeBodyBgColor = false;
        $model->useDefaultThemeBodyBgDarkColor = false;

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

    public function testDefaultSurfaceColorIsNotChecked()
    {
        $model = new ThemeSettingsForm();
        $model->useDefaultThemeBodyBgColor = true;
        $model->themeBodyBgColor = '#555555';

        $this->assertTrue($model->validate(['themeBodyBgColor']));
    }
}
