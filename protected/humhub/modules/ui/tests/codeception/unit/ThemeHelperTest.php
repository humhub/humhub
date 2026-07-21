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
        $theme = ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME);
        $this->testTheme($theme);

        // Clear the assets folder
        Yii::$app->assetManager->clear();
        $this->assertFalse(Yii::$app->assetManager->fileExists($this->getThemeCssPath($theme)));

        $this->assertTrue(ThemeHelper::buildCss($theme));
        $this->assertTrue(Yii::$app->assetManager->fileExists($this->getThemeCssPath($theme)));
    }

    public function testBuildCssForChildTheme()
    {
        $this->testTheme($this->createTheme('Test'));
    }

    public function testGetThemeByPathIgnoresThemeWithoutVariablesFile()
    {
        // An empty/incomplete theme directory (e.g. a `themes/HumHub` skeleton left
        // behind by an update) must not be loaded as a theme.
        $incompleteThemeDir = Yii::getAlias('@runtime') . '/tests/themes/Incomplete';
        FileHelper::removeDirectory($incompleteThemeDir);
        FileHelper::createDirectory($incompleteThemeDir . '/scss');

        $this->assertNull(ThemeHelper::getThemeByPath($incompleteThemeDir));

        FileHelper::removeDirectory($incompleteThemeDir);
    }

    public function testGetThemesByPathSkipsIncompleteTheme()
    {
        // A leftover `HumHub` skeleton (no scss/variables.scss) in the themes directory
        // must not be listed as a theme - otherwise it would shadow the real core theme.
        $themesDir = Yii::getAlias('@runtime') . '/tests/themes-scan';
        FileHelper::removeDirectory($themesDir);
        FileHelper::createDirectory($themesDir . '/HumHub/scss');

        $this->assertArrayNotHasKey('HumHub', ThemeHelper::getThemesByPath($themesDir));

        FileHelper::removeDirectory($themesDir);
    }

    private function testTheme(?Theme $theme)
    {
        $this->assertInstanceOf(Theme::class, $theme);
        $this->assertTrue(ThemeHelper::buildCss($theme));
        $this->assertTrue(Yii::$app->assetManager->fileExists($this->getThemeCssPath($theme)));
        $this->assertEquals('#435f6f', $theme->variable('primary'));
    }

    private function createTheme(string $newThemeName): ?Theme
    {
        $sourceThemeDir = ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME)->getBasePath();
        $newThemeDir = Yii::getAlias('@runtime') . '/tests/themes/' . $newThemeName;

        FileHelper::removeDirectory($newThemeDir);
        FileHelper::copyDirectory($sourceThemeDir, $newThemeDir, ['recursive' => true]);

        return ThemeHelper::getThemeByPath($newThemeDir);
    }

    private function getThemeCssPath(Theme $theme): string
    {
        return $theme->getPublishedResourcesPath() . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'theme.css';
    }
}
