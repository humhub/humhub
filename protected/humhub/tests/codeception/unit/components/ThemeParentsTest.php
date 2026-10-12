<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\Theme;
use humhub\helpers\ThemeHelper;
use ReflectionProperty;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\helpers\FileHelper;

/**
 * Tests the lookup of the parent themes of the active theme, which are
 * cached in the "themeParents" setting.
 *
 * @see Theme::getParents()
 */
class ThemeParentsTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    private const CHILD_NAME = 'ThemeParentsTestChild';
    private const PARENT_NAME = 'ThemeParentsTestParent';

    /**
     * @var string temporary working directory, recreated for every test
     */
    private string $tmpDir;

    private $originalTheme;

    protected function _before()
    {
        parent::_before();

        $this->tmpDir = Yii::getAlias('@runtime') . '/theme-parents-test-' . uniqid();
        $this->originalTheme = Yii::$app->view->theme;
        Yii::$app->settings->delete('themeParents');
    }

    protected function _after()
    {
        Yii::$app->view->theme = $this->originalTheme;
        $this->setThemes(null);
        Yii::$app->settings->delete('themeParents');

        if (isset($this->tmpDir) && is_dir($this->tmpDir)) {
            FileHelper::removeDirectory($this->tmpDir);
        }

        parent::_after();
    }

    private function createTheme(string $name, ?string $baseTheme = null): Theme
    {
        $basePath = $this->tmpDir . '/' . $name;
        FileHelper::createDirectory($basePath . '/scss');
        file_put_contents(
            $basePath . '/scss/variables.scss',
            $baseTheme === null ? '$primary: #123456;' : '$baseTheme: "' . $baseTheme . '";',
        );

        return new Theme(['name' => $name, 'basePath' => $basePath]);
    }

    /**
     * Replaces the themes known to ThemeHelper, so the real theme directories are not scanned
     *
     * @param Theme[]|null $themes
     */
    private function setThemes(?array $themes): void
    {
        $property = new ReflectionProperty(ThemeHelper::class, '_themes');
        $property->setValue(null, $themes);
    }

    private function activate(Theme $theme): void
    {
        Yii::$app->view->theme = $theme;
    }

    public function testParentsAreResolvedAndStored()
    {
        $parent = $this->createTheme(static::PARENT_NAME);
        $child = $this->createTheme(static::CHILD_NAME, static::PARENT_NAME);
        $this->setThemes([static::PARENT_NAME => $parent, static::CHILD_NAME => $child]);
        $this->activate($child);

        $this->assertSame([static::PARENT_NAME => $parent], $child->getParents());
        $this->assertSame([$parent->getBasePath()], Yii::$app->settings->getSerialized('themeParents'));
    }

    public function testStoredEmptyParentsAreIgnoredWhenBaseThemeIsDeclared()
    {
        $parent = $this->createTheme(static::PARENT_NAME);
        $child = $this->createTheme(static::CHILD_NAME, static::PARENT_NAME);
        $this->setThemes([static::PARENT_NAME => $parent, static::CHILD_NAME => $child]);
        $this->activate($child);

        // Stored while the base theme could not be found, e.g. its module was not loaded
        Yii::$app->settings->setSerialized('themeParents', []);

        $this->assertSame([static::PARENT_NAME => $parent], $child->getParents());
        $this->assertSame([$parent->getBasePath()], Yii::$app->settings->getSerialized('themeParents'));
    }

    public function testUnresolvedBaseThemeIsNotStored()
    {
        $child = $this->createTheme(static::CHILD_NAME, static::PARENT_NAME);
        $this->setThemes([static::CHILD_NAME => $child]);
        $this->activate($child);

        $this->assertSame([], $child->getParents());
        $this->assertNull(Yii::$app->settings->get('themeParents'));
    }

    public function testEmptyParentsAreStoredWithoutBaseTheme()
    {
        $theme = $this->createTheme(static::CHILD_NAME);
        $this->setThemes([static::CHILD_NAME => $theme]);
        $this->activate($theme);

        $this->assertSame([], $theme->getParents());
        $this->assertSame([], Yii::$app->settings->getSerialized('themeParents'));
    }
}
