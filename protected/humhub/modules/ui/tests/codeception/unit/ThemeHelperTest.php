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
        $theme = ThemeHelper::getThemeByName('HumHub');
        $this->testTheme($theme);

        // Clear the assets folder
        Yii::$app->assetManager->clear();
        $this->assertFileNotExists($this->getThemeCssPath($theme));

        $this->assertTrue(ThemeHelper::buildCss($theme));
        $this->assertFileExists($this->getThemeCssPath($theme));
    }

    public function testBuildCssForChildTheme()
    {
        $this->testTheme($this->createTheme('Test'));
    }

    private function testTheme(?Theme $theme)
    {
        $this->assertInstanceOf(Theme::class, $theme);
        $this->assertTrue(ThemeHelper::buildCss($theme));
        $this->assertFileExists($this->getThemeCssPath($theme));
        $this->assertEquals('#1b8291', $theme->variable('primary'));
    }

    private function createTheme(string $newThemeName): ?Theme
    {
        $sourceThemeDir = ThemeHelper::getThemeByName('HumHub')->getBasePath();
        $newThemeDir = Yii::getAlias('@webroot/themes') . '/' . $newThemeName;

        FileHelper::removeDirectory($newThemeDir);
        FileHelper::copyDirectory($sourceThemeDir, $newThemeDir, ['recursive' => true]);

        return ThemeHelper::getThemeByPath($newThemeDir);
    }

    private function getThemeCssPath(Theme $theme): string
    {
        return $theme->getPublishedResourcesPath() . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'theme.css';
    }
}
