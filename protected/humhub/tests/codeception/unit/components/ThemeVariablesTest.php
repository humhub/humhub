<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\Theme;
use humhub\models\Setting;
use ReflectionProperty;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\FileHelper;

/**
 * Tests the on-demand population of theme variables into the settings
 * manager, especially its behaviour under concurrent requests.
 *
 * @see \humhub\components\ThemeVariables::ensureLoaded()
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
    }

    protected function _after()
    {
        if (isset($this->themeBase) && is_dir($this->themeBase)) {
            FileHelper::removeDirectory($this->themeBase);
        }

        parent::_after();
    }

    /**
     * Builds a ThemeVariables instance for a temporary theme. Parent themes
     * are forced to an empty list so neither the database nor the real
     * `themes/` directory is consulted during the test.
     */
    private function makeThemeVariables(): ThemeVariablesMock
    {
        $theme = new Theme(['name' => static::THEME_NAME, 'basePath' => $this->themeBase]);

        $parents = new ReflectionProperty(Theme::class, 'parents');
        $parents->setAccessible(true);
        $parents->setValue($theme, []);

        return new ThemeVariablesMock(['theme' => $theme]);
    }

    public function testStoresVariablesWhenEmpty()
    {
        $variables = $this->makeThemeVariables();

        $this->assertEquals('#123456', $variables->get('primary'));
        $this->assertEquals(1, $variables->storeCount);
        $this->assertRecordExists(Setting::tableName(), [
            'name' => 'theme.var.' . static::THEME_NAME . '.primary',
            'module_id' => 'base',
        ]);

        // Further reads must not trigger another store
        $variables->get('primary');
        $this->assertEquals(1, $variables->storeCount);
    }

    public function testStoreVariablesRunsUnderMutex()
    {
        $mutex = new MutexMock();
        Yii::$app->set('mutex', $mutex);

        $variables = $this->makeThemeVariables();
        $variables->get('primary');

        $this->assertEquals(1, $variables->storeCount);
        $this->assertCount(1, $mutex->acquiredLocks);
        $this->assertStringContainsString(static::THEME_NAME, $mutex->acquiredLocks[0]);
        $this->assertEquals($mutex->acquiredLocks, $mutex->releasedLocks);
    }

    public function testSkipsStoreWhenConcurrentlyPopulated()
    {
        // Make sure the settings manager runtime cache is initialized,
        // so it is stale after the direct insert below
        Yii::$app->settings->get('name');

        // Simulate a concurrent request having stored the variables already —
        // bypassing the settings manager and its runtime cache
        Yii::$app->db->createCommand()->insert(Setting::tableName(), [
            'module_id' => 'base',
            'name' => 'theme.var.' . static::THEME_NAME . '.primary',
            'value' => '#abcdef',
        ])->execute();

        $variables = $this->makeThemeVariables();

        $this->assertEquals('#abcdef', $variables->get('primary'));
        $this->assertEquals(0, $variables->storeCount);
    }

    public function testStoresVariablesWhenMutexUnavailable()
    {
        $mutex = new MutexMock(['available' => false]);
        Yii::$app->set('mutex', $mutex);

        $variables = $this->makeThemeVariables();

        // Even without the lock the variables must be stored — set() is
        // race-safe — but a lock that was never acquired must not be released
        $this->assertEquals('#123456', $variables->get('primary'));
        $this->assertEquals(1, $variables->storeCount);
        $this->assertCount(1, $mutex->acquiredLocks);
        $this->assertCount(0, $mutex->releasedLocks);
    }
}
