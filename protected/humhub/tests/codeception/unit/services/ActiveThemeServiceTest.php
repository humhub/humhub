<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\services;

use humhub\components\Theme;
use humhub\helpers\ThemeHelper;
use humhub\services\ActiveThemeService;
use ReflectionProperty;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The resolved state of the active theme lives in a single `theme.state` setting
 * and is validated against four conditions: the theme name, the system revision, the
 * Custom SCSS, and the modification time of every `scss/variables.scss` feeding the
 * variables - the theme tree plus the core resources directory.
 *
 * @see ActiveThemeService
 * @since 1.20
 */
class ActiveThemeServiceTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    protected function _before()
    {
        parent::_before();

        Yii::$app->settings->set('theme', Theme::CORE_THEME_NAME);
        ActiveThemeService::flush();
    }

    /**
     * Drops the per-request memoization without dropping the stored row, so the
     * next getState() has to decide based on the stored state alone.
     */
    private function forgetMemoizedState(): void
    {
        (new ReflectionProperty(ActiveThemeService::class, 'state'))->setValue(null, null);
        (new ReflectionProperty(ActiveThemeService::class, 'resolved'))->setValue(null, false);
        (new ReflectionProperty(ActiveThemeService::class, 'theme'))->setValue(null, null);
        (new ReflectionProperty(ActiveThemeService::class, 'parents'))->setValue(null, null);
    }

    /**
     * Builds the state, then marks the stored row so a rebuild becomes visible:
     * the marker survives exactly as long as the state is considered valid.
     */
    private function markStoredState(): void
    {
        $state = ActiveThemeService::getState();
        $state['vars']['marker'] = 'kept';
        Yii::$app->settings->setSerialized(ActiveThemeService::SETTING_KEY, $state);

        $this->forgetMemoizedState();
    }

    private function storedStateIsMarked(): bool
    {
        return (ActiveThemeService::getVariables()['marker'] ?? null) === 'kept';
    }

    public function testBuildsStateForTheActiveTheme()
    {
        $coreTheme = ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME);
        $state = ActiveThemeService::getState();

        $this->assertSame(Theme::CORE_THEME_NAME, $state['name']);
        $this->assertSame($coreTheme->getBasePath(), $state['path']);
        $this->assertNotEmpty($state['vars']['primary']);
        $this->assertArrayHasKey($coreTheme->getBasePath(), $state['mtimes']);
        $this->assertArrayHasKey(Yii::getAlias('@humhub/resources'), $state['mtimes']);
    }

    public function testKeepsAValidState()
    {
        $this->markStoredState();

        $this->assertTrue($this->storedStateIsMarked());
    }

    public function testRebuildsWhenTheThemeNameChanged()
    {
        $this->markStoredState();

        Yii::$app->settings->set('theme', 'SomeOtherTheme');

        $this->assertFalse($this->storedStateIsMarked());
    }

    public function testRebuildsWhenTheSystemRevisionChanged()
    {
        $this->markStoredState();

        Yii::$app->systemRevision->touch();

        $this->assertFalse($this->storedStateIsMarked());
    }

    public function testRebuildsWhenTheCustomScssChanged()
    {
        $this->markStoredState();

        try {
            Yii::$app->settings->set('themeCustomScss', '$x: #010203;');

            $this->assertFalse($this->storedStateIsMarked());
        } finally {
            Yii::$app->settings->delete('themeCustomScss');
        }
    }

    public function testRebuildsWhenAVariablesFileChanged()
    {
        $this->markStoredState();

        $state = Yii::$app->settings->getSerialized(ActiveThemeService::SETTING_KEY);
        $state['mtimes'] = array_map(static fn() => 1, $state['mtimes']);
        Yii::$app->settings->setSerialized(ActiveThemeService::SETTING_KEY, $state);
        $this->forgetMemoizedState();

        $this->assertFalse($this->storedStateIsMarked());
    }

    /**
     * Stands for the moved-installation scenario, the case that rules out storing
     * absolute paths without a check on them.
     */
    public function testRebuildsWhenAStoredPathHasNoVariablesFile()
    {
        $this->markStoredState();

        $state = Yii::$app->settings->getSerialized(ActiveThemeService::SETTING_KEY);
        $state['path'] = '/does/not/exist';
        $state['mtimes'] = ['/does/not/exist' => 1];
        Yii::$app->settings->setSerialized(ActiveThemeService::SETTING_KEY, $state);
        $this->forgetMemoizedState();

        $this->assertFalse($this->storedStateIsMarked());
    }

    /**
     * An unresolvable name must fall back to the core theme without overwriting
     * the admin's choice - otherwise a temporarily disabled module providing the
     * theme would destroy it. The stored name therefore stays the requested one,
     * which is also what keeps the state valid instead of rebuilding on every
     * single request.
     */
    public function testFallsBackToTheCoreThemeForAnUnknownName()
    {
        $coreTheme = ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME);

        Yii::$app->settings->set('theme', 'DoesNotExist');
        ActiveThemeService::flush();

        $state = ActiveThemeService::getState();
        $this->assertSame('DoesNotExist', $state['name']);
        $this->assertSame($coreTheme->getBasePath(), $state['path']);

        // No rebuild loop: the state stays valid on the next request
        $this->markStoredState();
        $this->assertTrue($this->storedStateIsMarked());
    }

    /**
     * A fresh installation has no `theme` setting until the first Theme::activate().
     * The state must still settle on the core theme AND stay valid - storing the
     * resolved name here instead of the requested one rebuilt on every single request.
     */
    public function testAnEmptyThemeSettingSettlesOnTheCoreThemeWithoutRebuilding()
    {
        $coreTheme = ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME);

        Yii::$app->settings->delete('theme');
        ActiveThemeService::flush();

        $this->assertSame($coreTheme->getBasePath(), ActiveThemeService::getState()['path']);

        $this->markStoredState();
        $this->assertTrue($this->storedStateIsMarked());
    }

    /**
     * A truncated or hand-edited row makes getSerialized() return the raw string
     * rather than null, so the validation has to survive being handed a non-array.
     */
    public function testRebuildsWhenTheStoredStateIsNotValidJson()
    {
        ActiveThemeService::getState();
        Yii::$app->settings->set(ActiveThemeService::SETTING_KEY, '{"name":"HumHub её');
        $this->forgetMemoizedState();

        $this->assertSame(Theme::CORE_THEME_NAME, ActiveThemeService::getState()['name']);
    }

    /**
     * The Custom SCSS is admin-supplied and may not compile. That must not take the
     * site down, so the state is built without it rather than not at all.
     */
    public function testBuildsTheStateEvenWhenTheCustomScssDoesNotCompile()
    {
        try {
            Yii::$app->settings->set('themeCustomScss', '$x: (unclosed');
            ActiveThemeService::flush();

            $this->assertNotEmpty(ActiveThemeService::getState()['vars']['primary']);
        } finally {
            Yii::$app->settings->delete('themeCustomScss');
        }
    }

    public function testGetThemeResolvesTheStoredPath()
    {
        $coreTheme = ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME);

        $this->assertSame(Theme::CORE_THEME_NAME, ActiveThemeService::getTheme()->name);
        $this->assertSame($coreTheme->getBasePath(), ActiveThemeService::getTheme()->getBasePath());
    }

    /**
     * The parent chain is stored as plain paths, so reading it back has to produce
     * Theme instances keyed by name - the shape ThemeHelper::getThemeTree() returns.
     */
    public function testGetParentsResolvesTheStoredPathsKeyedByName()
    {
        $coreTheme = ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME);

        // The core theme is the only one shipped, so it stands in for a parent here
        $state = ActiveThemeService::getState();
        $state['parents'] = [$coreTheme->getBasePath()];
        Yii::$app->settings->setSerialized(ActiveThemeService::SETTING_KEY, $state);
        $this->forgetMemoizedState();

        $parents = ActiveThemeService::getParents();

        $this->assertSame([Theme::CORE_THEME_NAME], array_keys($parents));
        $this->assertSame($coreTheme->getBasePath(), $parents[Theme::CORE_THEME_NAME]->getBasePath());
    }

    /**
     * The memo guard has to tell "not resolved yet" apart from "resolved to nothing",
     * which an emptiness check cannot - the core theme has no parents, so that is the
     * ordinary case rather than an exotic one.
     */
    public function testGetParentsMemoizesAnEmptyChain()
    {
        $coreTheme = ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME);

        // Compare the keys, never the Theme instances: exporting one for a failure
        // message walks the whole application object graph
        $this->assertSame([], array_keys(ActiveThemeService::getParents()));

        $this->assertSame(
            [],
            (new ReflectionProperty(ActiveThemeService::class, 'parents'))->getValue(),
            'An empty parent chain must be memoized',
        );

        // Plant a parent in the memoized state: only a second resolution would pick it
        // up, so seeing it proves the memo was not used
        $state = ActiveThemeService::getState();
        $state['parents'] = [$coreTheme->getBasePath()];
        (new ReflectionProperty(ActiveThemeService::class, 'state'))->setValue(null, $state);

        $this->assertSame(
            [],
            array_keys(ActiveThemeService::getParents()),
            'An empty parent chain must not be re-resolved on every call',
        );
    }

    /**
     * Theme::activate() flushes and then expects the next read to see the new theme.
     * Losing any of these resets would keep handing out the previous one for the rest
     * of the request - invisible in every other test.
     */
    public function testFlushDropsTheMemoizedObjects()
    {
        $before = ActiveThemeService::getTheme();
        ActiveThemeService::getParents();

        ActiveThemeService::flush();

        $after = ActiveThemeService::getTheme();
        $this->assertSame(Theme::CORE_THEME_NAME, $after->name);
        $this->assertNotSame($before, $after);

        ActiveThemeService::flush();
        foreach (['state', 'theme', 'parents', 'resolved'] as $property) {
            $this->assertContains(
                (new ReflectionProperty(ActiveThemeService::class, $property))->getValue(),
                [null, false],
                'flush() must drop the memoized ' . $property,
            );
        }
    }

    public function testFlushDropsTheStoredState()
    {
        ActiveThemeService::getState();
        ActiveThemeService::flush();

        $this->assertNull(Yii::$app->settings->get(ActiveThemeService::SETTING_KEY));
    }
}
