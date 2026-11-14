<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\Theme;
use humhub\components\ThemeVariables;
use humhub\services\ActiveThemeService;
use ReflectionProperty;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\FileHelper;

/**
 * The variables of the active theme are read from the single `theme.state` setting;
 * any other theme is read from its SCSS files and never persisted, so that setting
 * stays a single row.
 *
 * @see ThemeVariables
 */
class ThemeVariablesTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    private const THEME_NAME = 'ThemeVariablesTestTheme';

    /**
     * @var string temporary theme base path, recreated for every test
     */
    private string $themeBase;

    protected function _before()
    {
        $this->themeBase = Yii::getAlias('@runtime') . '/theme-variables-test-' . uniqid();
        FileHelper::createDirectory($this->themeBase . '/scss');
        file_put_contents($this->themeBase . '/scss/variables.scss', '$primary: #123456;');

        parent::_before();

        Yii::$app->settings->set('theme', Theme::CORE_THEME_NAME);
        ActiveThemeService::flush();
    }

    protected function _after()
    {
        if (isset($this->themeBase) && is_dir($this->themeBase)) {
            FileHelper::removeDirectory($this->themeBase);
        }

        // The settings are restored by the fixtures, the memos of the service are not -
        // they would otherwise carry a manipulated state into every following test file
        ActiveThemeService::flush();

        parent::_after();
    }

    /**
     * Builds a ThemeVariables instance for a temporary theme that is not the active one.
     * Its variables come from the temporary `variables.scss` alone: the theme declares no
     * `baseTheme`, so the tree {@see \humhub\helpers\ThemeHelper::getAllVariables()} walks
     * is the theme itself.
     */
    private function makeNonActiveThemeVariables(): ThemeVariables
    {
        return new ThemeVariables([
            'theme' => new Theme(['name' => static::THEME_NAME, 'basePath' => $this->themeBase]),
        ]);
    }

    public function testReadsTheActiveThemeFromTheState()
    {
        // A value that cannot come from the SCSS files, so reading it proves the state
        // is the source rather than a live read that happens to agree
        $state = ActiveThemeService::getState();
        $state['vars']['primary'] = '#feedee';
        Yii::$app->settings->setSerialized(ActiveThemeService::SETTING_KEY, $state);

        // Drop the in-request memos so the manipulated row is read back. `resolved` has
        // to go with `state`, otherwise the service short-circuits to null instead
        (new ReflectionProperty(ActiveThemeService::class, 'state'))->setValue(null, null);
        (new ReflectionProperty(ActiveThemeService::class, 'resolved'))->setValue(null, false);

        $variables = new ThemeVariables(['theme' => ActiveThemeService::getTheme()]);

        $this->assertSame('#feedee', $variables->get('primary'));
    }

    public function testReadsANonActiveThemeLiveWithoutPersistingIt()
    {
        // Materialize the state of the active theme first, so the comparison below sees
        // the read of the non-active theme and not the one row the service legitimately
        // writes for the active one
        ActiveThemeService::getState();
        $stateBefore = Yii::$app->settings->getSerialized(ActiveThemeService::SETTING_KEY);

        $variables = $this->makeNonActiveThemeVariables();

        $this->assertSame('#123456', $variables->get('primary'));
        $this->assertSame(
            $stateBefore,
            Yii::$app->settings->getSerialized(ActiveThemeService::SETTING_KEY),
        );
    }

    public function testReturnsTheDefaultForAnUnknownVariable()
    {
        $variables = $this->makeNonActiveThemeVariables();

        $this->assertSame('fallback', $variables->get('no-such-variable', 'fallback'));
    }

    /**
     * The nine `theme<Color>Color` settings are their own rows and keep overriding the
     * variables of the active theme at read time.
     */
    public function testCustomColorSettingOverridesTheThemeVariable()
    {
        Yii::$app->settings->set('themePrimaryColor', '#abcdef');

        $variables = new ThemeVariables(['theme' => ActiveThemeService::getTheme()]);

        $this->assertSame('#abcdef', $variables->get('primary'));
    }

    /**
     * A variable derived from another one comes as the colour it compiles to, not as its SCSS expression
     */
    public function testDerivedVariablesAreResolved()
    {
        $variables = new ThemeVariables(['theme' => ActiveThemeService::getTheme()]);

        $this->assertSame('#ededed', $variables->get('hh-page-bg'));
        $this->assertSame('#f9f9f9', $variables->get('body-tertiary-bg'));
        $this->assertSame('#dddddd', $variables->get('border-color'));
        $this->assertSame('#161616', $variables->get('hh-page-bg-dark'));
        $this->assertSame('#393939', $variables->get('body-secondary-bg-dark'));
        $this->assertSame('#e7e7e7', $variables->get('light'));
    }

    /**
     * The page and the other surfaces are steps from the surface colour of Administration → Appearance → Theme
     */
    public function testSurfaceColorSettingDerivesThePage()
    {
        Yii::$app->settings->set('themeBodyBgColor', '#fdf8ec');
        Yii::$app->settings->set('useDefaultThemeBodyBgColor', false);
        Yii::$app->settings->set('themeBodyBgDarkColor', '#1b2a44');
        Yii::$app->settings->set('useDefaultThemeBodyBgDarkColor', false);
        ActiveThemeService::flush();

        $variables = new ThemeVariables(['theme' => ActiveThemeService::getTheme()]);

        $this->assertSame('#fdf8ec', $variables->get('body-bg'));
        $this->assertSame('#ebe7db', $variables->get('hh-page-bg'));
        $this->assertSame('#1b2a44', $variables->get('body-bg-dark'));
        $this->assertSame('#121b2c', $variables->get('hh-page-bg-dark'));
    }

    /**
     * A theme colour as text comes as the shade that reads on the surfaces and on its own subtle fill, e.g. for the
     * links of a mail
     */
    public function testThemeColorsAsTextAreResolved()
    {
        $variables = new ThemeVariables(['theme' => ActiveThemeService::getTheme()]);

        $this->assertSame('#435f6f', $variables->get('hh-primary-fg'));
        $this->assertSame('#176e7b', $variables->get('hh-accent-fg'));
        $this->assertSame('#69adb6', $variables->get('hh-accent-fg-dark'));
    }

    /**
     * Muted text is the body text, the placeholder and the disabled colour are greys between the body text and the
     * surface, and the code reads on its fill (docs/develop/concept-design-tokens.md, "Text")
     */
    public function testTextColorsAreDerived()
    {
        $variables = new ThemeVariables(['theme' => ActiveThemeService::getTheme()]);

        $this->assertSame('#555555', $variables->get('body-secondary-color'));
        $this->assertSame('#757575', $variables->get('hh-input-placeholder-color'));
        $this->assertSame('#898989', $variables->get('hh-input-placeholder-color-dark'));
        $this->assertSame('#aaaaaa', $variables->get('body-tertiary-color'));
        $this->assertSame('#808080', $variables->get('body-tertiary-color-dark'));
        $this->assertSame('#197a88', $variables->get('code-color'));
    }

    /**
     * A text colour a theme sets is made readable all the same
     */
    public function testInkOfTheCustomScssIsMadeReadable()
    {
        Yii::$app->settings->set('themeCustomScss', '$body-secondary-color: #aaa; $hh-accent-fg: #8fd0da;');
        ActiveThemeService::flush();

        $variables = new ThemeVariables(['theme' => ActiveThemeService::getTheme()]);

        $this->assertSame('#6b6b6b', $variables->get('body-secondary-color'));
        $this->assertSame('#496a70', $variables->get('hh-accent-fg'));
    }

    /**
     * A tinted surface is its colour mixed into the surface, in the dark mode as in the light one
     */
    public function testTintedSurfacesFollowTheSurface()
    {
        $variables = new ThemeVariables(['theme' => ActiveThemeService::getTheme()]);

        $this->assertSame('#d1e6e9', $variables->get('accent-bg-subtle'));
        $this->assertSame('#213538', $variables->get('accent-bg-subtle-dark'));
        $this->assertSame('#edf5f6', $variables->get('hh-accent-bg-soft'));
        $this->assertSame('#fff3cd', $variables->get('mark-bg'));
    }

    /**
     * The border of an input is the line, darkened (lightened) to 3:1 on the surface (WCAG 1.4.11)
     */
    public function testInputBorderReachesThreeToOne()
    {
        $variables = new ThemeVariables(['theme' => ActiveThemeService::getTheme()]);

        $this->assertSame('#949494', $variables->get('hh-input-border-color'));
        $this->assertSame('#6d6d6d', $variables->get('hh-input-border-color-dark'));
    }

    /**
     * A derived value is resolved whether it refers to a variable or not
     */
    public function testDerivedValueWithoutAVariableIsResolved()
    {
        Yii::$app->settings->set('themeCustomScss', '$hh-page-bg: mix(black, #fff, 7%);');
        ActiveThemeService::flush();

        $variables = new ThemeVariables(['theme' => ActiveThemeService::getTheme()]);

        $this->assertSame('#ededed', $variables->get('hh-page-bg'));
    }

    /**
     * The "Default" of the theme settings is the theme's own value, whatever colour the admin chose
     */
    public function testThemeDefaultIgnoresTheCustomColorSetting()
    {
        Yii::$app->settings->set('themePrimaryColor', '#abcdef');

        $variables = new ThemeVariables(['theme' => ActiveThemeService::getTheme()]);

        $this->assertSame('#abcdef', $variables->get('primary'));
        $this->assertSame('#435f6f', $variables->getThemeDefault('primary'));
    }

    public function testFlushCacheDropsTheState()
    {
        $variables = new ThemeVariables(['theme' => Yii::$app->view->theme]);
        ActiveThemeService::getState();

        $variables->flushCache();

        $this->assertNull(Yii::$app->settings->get(ActiveThemeService::SETTING_KEY));
    }
}
