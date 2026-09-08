<?php

namespace tests\codeception\unit;

use humhub\components\Theme;
use humhub\helpers\ThemeHelper;
use humhub\modules\file\libs\FileHelper;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class ThemeTest extends HumHubDbTestCase
{
    /**
     * The path a broken theme would write its CSS to, since its published base path is empty.
     */
    private const STRAY_CSS_FILE = 'resources/css/theme.css';

    public function testPublishSkipsMissingThemeDirectory()
    {
        $theme = $this->createRemovedTheme();

        $this->assertSame('', $theme->publishResources());
        $this->assertSame('', $theme->getPublishedBasePath());
        $this->assertSame('', $theme->getBaseUrl());
    }

    /**
     * A theme whose source directory is gone can neither be published nor built from,
     * so `register()` has to switch to the core theme - otherwise the CSS is written to
     * the root of the assets mount and registered as a URL relative to the domain root.
     */
    public function testRegisterFallsBackToCoreThemeOnMissingThemeDirectory()
    {
        $theme = $this->createRemovedTheme();
        $coreTheme = ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME);

        // Drop a stray file left behind by an earlier run, it would mask the assertion below
        if (Yii::$app->assetManager->fileExists(self::STRAY_CSS_FILE)) {
            Yii::$app->fs->getAssetsMount()->delete(self::STRAY_CSS_FILE);
        }

        $previousTheme = Yii::$app->view->theme;
        $previousRequestUri = $_SERVER['REQUEST_URI'] ?? null;

        Yii::$app->view->theme = $theme;
        Yii::$app->settings->set('theme', $theme->getBasePath());
        $_SERVER['REQUEST_URI'] = '/';

        try {
            $theme->register();

            $this->assertSame($coreTheme->getBasePath(), Yii::$app->settings->get('theme'));
            $this->assertFalse(Yii::$app->assetManager->fileExists(self::STRAY_CSS_FILE));
        } finally {
            Yii::$app->view->theme = $previousTheme;
            if ($previousRequestUri === null) {
                unset($_SERVER['REQUEST_URI']);
            } else {
                $_SERVER['REQUEST_URI'] = $previousRequestUri;
            }
        }
    }

    /**
     * The theme CSS is rebuilt inside an unchanged published directory, so without the
     * modification time in the URL a changed theme never reaches a browser that has
     * already cached it.
     */
    public function testRegisterAddsModificationTimeToThemeCssUrl()
    {
        $theme = ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME);
        $this->assertTrue(ThemeHelper::buildCss($theme, false));

        $previousTheme = Yii::$app->view->theme;
        Yii::$app->view->theme = $theme;

        try {
            $theme->register();

            $cssUrls = array_values(array_filter(
                array_keys(Yii::$app->view->assetBundles),
                fn(string $key) => str_contains($key, 'theme.css'),
            ));

            $this->assertCount(1, $cssUrls);
            $this->assertMatchesRegularExpression('/theme\.css\?v=\d+$/', $cssUrls[0]);
        } finally {
            Yii::$app->view->theme = $previousTheme;
        }
    }

    /**
     * Returns a valid theme whose directory is removed right after it has been resolved,
     * e.g. an update moving the theme or a stale stored theme path.
     */
    private function createRemovedTheme(): Theme
    {
        $themeDir = Yii::getAlias('@runtime') . '/tests/themes/Removed';

        FileHelper::removeDirectory($themeDir);
        FileHelper::copyDirectory(
            ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME)->getBasePath(),
            $themeDir,
            ['recursive' => true],
        );

        $theme = ThemeHelper::getThemeByPath($themeDir);
        $this->assertInstanceOf(Theme::class, $theme);

        FileHelper::removeDirectory($themeDir);

        return $theme;
    }
}
