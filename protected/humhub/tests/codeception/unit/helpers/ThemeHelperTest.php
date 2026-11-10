<?php

namespace humhub\tests\codeception\unit\helpers;

use humhub\components\Theme;
use humhub\helpers\ScssHelper;
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

    public function testBuildCssPutsCoreAndThemeIntoCascadeLayers()
    {
        $theme = $this->createTheme('Layers');
        file_put_contents($theme->getBasePath() . '/scss/build.scss', '.layers-test-theme { color: red; }');
        $this->assertTrue(ThemeHelper::buildCss($theme, false));

        $css = Yii::$app->fs->getAssetsMount()->read(Yii::$app->assetManager->normalizePath($this->getThemeCssPath($theme)));
        $this->assertMatchesRegularExpression('/@layer core, ?theme;/', $css);

        // The core, then the theme, then the utilities outside of the layers
        $this->assertMatchesRegularExpression('/@layer core ?\{/', $css);
        $this->assertMatchesRegularExpression('/@layer theme ?\{[^@]*\.layers-test-theme/', $css);
        $this->assertGreaterThan(strpos($css, '.layers-test-theme'), strpos($css, '.d-none{'));
    }

    public function testThemeSettingAFormerColorVariableSetsTheNewOne()
    {
        $theme = $this->createTheme('FormerVariables');
        file_put_contents($theme->getBasePath() . '/scss/variables.scss', '$text-color-secondary: #4b4b4b;', FILE_APPEND);
        $this->assertTrue(ThemeHelper::buildCss($theme, false));

        $css = Yii::$app->fs->getAssetsMount()->read(Yii::$app->assetManager->normalizePath($this->getThemeCssPath($theme)));
        $this->assertMatchesRegularExpression('/--bs-secondary-color: ?#4b4b4b/', $css);
        $this->assertEquals('#4b4b4b', $theme->variable('body-secondary-color'));
        // A module reading the former name
        $this->assertEquals('#4b4b4b', $theme->variable('text-color-secondary'));
    }

    /**
     * The core variables as colours, derived ones (`$hh-page-bg: mix(black, $body-bg, 7%)`) resolved, as the mail
     * layouts read them (`MailStyleHelper`)
     */
    public function testCoreVariablesResolveToColors()
    {
        $variables = ThemeHelper::getAllVariables(ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME), false);

        $this->assertEquals('#555', $variables['body-color']);
        $this->assertEquals('#fff', $variables['body-bg']);
        $this->assertEquals('#ededed', $variables['hh-page-bg']);
        // The former names (deprecated since 1.20)
        $this->assertEquals('#555', $variables['text-color-main']);
        $this->assertEquals('#ededed', $variables['background-color-page']);
        $this->assertEquals('#fff', $variables['background-color-main']);
    }

    /**
     * A theme colour as text is its `--hh-{color}-fg`, in the utilities, the components and the high contrast tokens
     */
    public function testBuildCssWiresTheThemeColorsAsText()
    {
        $theme = $this->createTheme('ColorsAsText');
        $this->assertTrue(ThemeHelper::buildCss($theme, false));

        $css = Yii::$app->fs->getAssetsMount()->read(Yii::$app->assetManager->normalizePath($this->getThemeCssPath($theme)));
        $this->assertMatchesRegularExpression('/:root\{[^}]*--hh-accent-fg: ?#197684/', $css);
        $this->assertMatchesRegularExpression('/\.text-accent\{[^}]*color: ?var\(--hh-accent-fg\)/', $css);
        $this->assertMatchesRegularExpression('/\.link-accent\{[^}]*color: ?var\(--hh-accent-fg\)/', $css);
        $this->assertMatchesRegularExpression('/\.btn-ghost-danger\{[^}]*--bs-btn-color: ?var\(--hh-danger-fg\)/', $css);
        $this->assertMatchesRegularExpression('/\.btn-outline-accent\{[^}]*--bs-btn-color: ?var\(--hh-accent-fg\)/', $css);
        // High contrast: the body text and its rgb twin, a fill and the pressed button from it
        $this->assertMatchesRegularExpression('/\[data-hh-contrast=more\]\{[^}]*--bs-body-color: ?#[0-9a-f]{6};--bs-body-color-rgb: ?\d/', $css);
        $this->assertMatchesRegularExpression('/\[data-hh-contrast=more\]\{[^}]*--bs-danger-rgb: ?\d/', $css);
        $this->assertMatchesRegularExpression('/\[data-hh-contrast=more\] \.btn-danger\{[^}]*--bs-btn-active-bg: ?color-mix\(in srgb, ?var\(--bs-danger\)/', $css);
    }

    /**
     * The text on a theme colour as a fill (a button, a badge, the top bar) reaches 4.5:1: white where it does, black
     * otherwise, the fill stays as chosen. The muted text reaches 4.5:1 on a surface
     */
    public function testBuildCssKeepsTheTextOnAFillReadable()
    {
        $theme = $this->createTheme('ReadableFills');
        $this->assertTrue(ThemeHelper::buildCss($theme, false));

        $css = Yii::$app->fs->getAssetsMount()->read(Yii::$app->assetManager->normalizePath($this->getThemeCssPath($theme)));
        // White reaches 4.6:1 on success, 1.6:1 on warning
        $this->assertMatchesRegularExpression('/:root,\[data-bs-theme=light\]\{[^}]*--bs-success: ?#438417\b/', $css);
        $this->assertMatchesRegularExpression('/:root\{[^}]*--hh-success-contrast: ?#fff\b/', $css);
        $this->assertMatchesRegularExpression('/:root,\[data-bs-theme=light\]\{[^}]*--bs-warning: ?#ffc107\b/i', $css);
        $this->assertMatchesRegularExpression('/:root\{[^}]*--hh-warning-contrast: ?#000\b/', $css);
        foreach (['primary', 'secondary', 'accent', 'info', 'success', 'warning', 'danger'] as $color) {
            $this->assertMatchesRegularExpression('/:root,\[data-bs-theme=light\]\{[^}]*--bs-' . $color . ': ?(#[0-9a-f]{3,6})\b/i', $css);
            preg_match('/:root,\[data-bs-theme=light\]\{[^}]*--bs-' . $color . ': ?(#[0-9a-f]{3,6})\b/i', $css, $fill);
            preg_match('/:root\{[^}]*--hh-' . $color . '-contrast: ?(#[0-9a-f]{3,6})\b/i', $css, $text);
            $this->assertGreaterThanOrEqual(4.5, ScssHelper::getContrastRatio($fill[1], $text[1]), "$text[1] on $color $fill[1]");
            $this->assertMatchesRegularExpression('/\.btn-' . $color . '\{--bs-btn-color: ?' . $text[1] . ';--bs-btn-bg: ?' . $fill[1] . '\b/i', $css);
        }

        preg_match('/:root,\[data-bs-theme=light\]\{[^}]*--bs-secondary-color: ?(#[0-9a-f]{3,6})\b/i', $css, $muted);
        $this->assertGreaterThanOrEqual(4.5, ScssHelper::getContrastRatio($muted[1], '#fff'), "muted text $muted[1]");
    }

    /**
     * The Custom SCSS brings back white text from 3:1 on (docs/admin/upgrade-v1.20.md)
     */
    public function testCustomScssLowersTheContrastRatioOfTheTextOnAFill()
    {
        $theme = $this->createTheme('LowerContrastRatio');
        $successContrast = function (string $customScss) use ($theme): string {
            Yii::$app->settings->set('themeCustomScss', $customScss);
            $this->assertTrue(ThemeHelper::buildCss($theme));
            $css = Yii::$app->fs->getAssetsMount()->read(Yii::$app->assetManager->normalizePath($this->getThemeCssPath($theme)));
            $this->assertMatchesRegularExpression('/:root\{[^}]*--hh-success-contrast: ?(#[0-9a-f]{3,6})\b/', $css);
            preg_match('/:root\{[^}]*--hh-success-contrast: ?(#[0-9a-f]{3,6})\b/', $css, $match);
            return $match[1];
        };

        try {
            // The former default: white reaches 3.2:1
            $this->assertEquals('#000', $successContrast('$success: #52a11c;'));
            $this->assertEquals('#fff', $successContrast("\$success: #52a11c;\n\$min-contrast-ratio: 3;"));
        } finally {
            Yii::$app->settings->delete('themeCustomScss');
        }
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
