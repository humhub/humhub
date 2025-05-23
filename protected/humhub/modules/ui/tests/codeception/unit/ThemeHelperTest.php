<?php

namespace tests\codeception\unit;

use humhub\components\Theme;
use humhub\helpers\ThemeHelper;
use humhub\modules\file\libs\FileHelper;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ThemeHelperTest extends HumHubDbTestCase
{
    public function testBuildCssForDefaultTheme()
    {
        $this->testTheme(ThemeHelper::getThemeByName('HumHub'));
    }

    public function testBuildCssForChildTheme()
    {
        $this->testTheme($this->createTheme('Test'));
    }

    private function testTheme(?Theme $theme)
    {
        $this->assertInstanceOf(Theme::class, $theme);
        $this->assertTrue(ThemeHelper::buildCss($theme));
        $this->assertFileExists($theme->getPublishedResourcesPath() . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'theme.css');
        $this->assertEquals('#435f6f', $theme->variable('primary'));
    }

    private function createTheme(string $newThemeName): ?Theme
    {
        $sourceThemeDir = ThemeHelper::getThemeByName('HumHub')->getBasePath();
        $newThemeDir = Yii::getAlias('@webroot/themes') . '/' . $newThemeName;

        FileHelper::removeDirectory($newThemeDir);
        FileHelper::copyDirectory($sourceThemeDir, $newThemeDir, ['recursive' => true]);

        return ThemeHelper::getThemeByPath($newThemeDir);
    }
}
