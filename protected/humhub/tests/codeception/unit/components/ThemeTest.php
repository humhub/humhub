<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

/**
 * @noinspection PhpIllegalPsrClassPathInspection
 */

namespace humhub\tests\codeception\unit\components;

use Codeception\Test\Unit;
use humhub\components\bootstrap\ThemeLoader;
use humhub\components\Theme;
use ReflectionMethod;
use ReflectionProperty;
use Yii;
use yii\base\Theme as BaseTheme;
use yii\helpers\FileHelper;

/**
 * Tests the `pathMap` based view override mechanism introduced for
 * `components.view.theme.pathMap` configuration in `common.php`.
 *
 * @see Theme::applyTo()
 * @see ThemeLoader
 */
class ThemeTest extends Unit
{
    /**
     * @var string temporary working directory, recreated for every test
     */
    private string $tmpDir;

    /**
     * @var string acts as the theme base path of the theme under test
     */
    private string $themeBase;

    protected function _before()
    {
        $this->tmpDir = Yii::getAlias('@runtime') . '/theme-path-map-test-' . uniqid();
        $this->themeBase = $this->tmpDir . '/theme';
        FileHelper::createDirectory($this->themeBase);
    }

    protected function _after()
    {
        if (isset($this->tmpDir) && is_dir($this->tmpDir)) {
            FileHelper::removeDirectory($this->tmpDir);
        }
    }

    /**
     * Creates a file (with parent directories) below the temp directory and
     * returns its absolute path.
     */
    private function createFile(string $relativePath): string
    {
        $full = $this->tmpDir . '/' . ltrim($relativePath, '/');
        FileHelper::createDirectory(dirname($full));
        file_put_contents($full, '<?php // fixture view');

        return $full;
    }

    /**
     * Builds a Theme instance with the given `pathMap` and a controlled base
     * path. Parent themes are forced to an empty list so neither the database
     * nor the real `themes/` directory is consulted during the test.
     */
    private function makeTheme($pathMap = null): Theme
    {
        $config = ['name' => 'UnitTestTheme', 'basePath' => $this->themeBase];
        if ($pathMap !== null) {
            $config['pathMap'] = $pathMap;
        }

        $theme = new Theme($config);

        $parents = new ReflectionProperty(Theme::class, 'parents');
        $parents->setAccessible(true);
        $parents->setValue($theme, []);

        return $theme;
    }

    // -- per-file overrides ---------------------------------------------------

    public function testPerFileOverrideResolvesToOverrideFile()
    {
        $source = $this->tmpDir . '/core/auth/login.php';
        $override = $this->createFile('overrides/login.php');

        $theme = $this->makeTheme([$source => $override]);

        $this->assertSame($override, $theme->applyTo($source));
    }

    public function testPerFileOverrideIgnoredWhenOverrideFileMissing()
    {
        $source = $this->tmpDir . '/core/auth/login.php';

        $theme = $this->makeTheme([$source => $this->tmpDir . '/overrides/does-not-exist.php']);

        // No override file on disk: applyTo() falls through and returns the original path.
        $this->assertSame($source, $theme->applyTo($source));
    }

    public function testPerFileOverrideUsesFirstExistingFallback()
    {
        $source = $this->tmpDir . '/core/auth/login.php';
        $missing = $this->tmpDir . '/overrides/missing.php';
        $first = $this->createFile('overrides/first.php');
        $second = $this->createFile('overrides/second.php');

        $theme = $this->makeTheme([$source => [$missing, $first, $second]]);

        $this->assertSame($first, $theme->applyTo($source));
    }

    public function testPerFileOverrideOnlyMatchesExactPath()
    {
        $mapped = $this->tmpDir . '/core/auth/login.php';
        $requested = $this->tmpDir . '/core/auth/register.php';
        $this->createFile('overrides/login.php');

        $theme = $this->makeTheme([$mapped => $this->tmpDir . '/overrides/login.php']);

        // A different view file must not pick up the login.php override.
        $this->assertSame($requested, $theme->applyTo($requested));
    }

    // -- directory overrides --------------------------------------------------

    public function testDirectoryOverrideResolvesViaPrefixSubstitution()
    {
        $sourceDir = $this->tmpDir . '/core/space-widgets';
        $overrideDir = $this->tmpDir . '/overrides/space-widgets';
        $override = $this->createFile('overrides/space-widgets/list/item.php');

        $theme = $this->makeTheme([$sourceDir => $overrideDir]);

        $resolved = $theme->applyTo($sourceDir . '/list/item.php');
        $this->assertSame(FileHelper::normalizePath($override), FileHelper::normalizePath($resolved));
    }

    public function testDirectoryOverrideIgnoredWhenFileMissingInOverrideDir()
    {
        $sourceDir = $this->tmpDir . '/core/space-widgets';
        $overrideDir = $this->tmpDir . '/overrides/space-widgets';
        $this->createFile('overrides/space-widgets/list/item.php');

        $theme = $this->makeTheme([$sourceDir => $overrideDir]);

        // item.php is overridden, other.php is not - the latter falls through.
        $requested = $sourceDir . '/list/other.php';
        $this->assertSame($requested, $theme->applyTo($requested));
    }

    public function testDirectoryOverrideUsesFirstExistingFallback()
    {
        $sourceDir = $this->tmpDir . '/core/space-widgets';
        $missingDir = $this->tmpDir . '/overrides/dir-a';
        $presentDir = $this->tmpDir . '/overrides/dir-b';
        $override = $this->createFile('overrides/dir-b/list/item.php');

        $theme = $this->makeTheme([$sourceDir => [$missingDir, $presentDir]]);

        $resolved = $theme->applyTo($sourceDir . '/list/item.php');
        $this->assertSame(FileHelper::normalizePath($override), FileHelper::normalizePath($resolved));
    }

    public function testPerFileOverrideFallsBackToDirectoryOverride()
    {
        $sourceDir = $this->tmpDir . '/core/space-widgets';
        $sourceFile = $sourceDir . '/list/item.php';
        $overrideDir = $this->tmpDir . '/overrides/space-widgets';
        $dirOverride = $this->createFile('overrides/space-widgets/list/item.php');

        // The per-file entry comes first but its target is missing; resolution
        // must continue and fall back to the directory entry.
        $theme = $this->makeTheme([
            $sourceFile => $this->tmpDir . '/overrides/missing-file.php',
            $sourceDir => $overrideDir,
        ]);

        $resolved = $theme->applyTo($sourceFile);
        $this->assertSame(FileHelper::normalizePath($dirOverride), FileHelper::normalizePath($resolved));
    }

    // -- alias handling -------------------------------------------------------

    public function testAliasesAreResolvedInKeysAndValues()
    {
        $relDir = basename($this->tmpDir);
        $source = $this->tmpDir . '/core/auth/login.php';
        $override = $this->createFile('overrides/login.php');

        // Both the key and the value are expressed as Yii path aliases.
        $theme = $this->makeTheme([
            '@runtime/' . $relDir . '/core/auth/login.php' => '@runtime/' . $relDir . '/overrides/login.php',
        ]);

        $this->assertSame($override, $theme->applyTo($source));
    }

    // -- initPathMap() --------------------------------------------------------

    public function testInitPathMapMergesUserEntriesWithDefaultViewMapping()
    {
        $source = $this->tmpDir . '/core/auth/login.php';
        $theme = $this->makeTheme([$source => $this->tmpDir . '/overrides/login.php']);

        // applyTo() triggers initPathMap() internally.
        $theme->applyTo($source);

        $this->assertArrayHasKey($source, $theme->pathMap, 'User-supplied entry must be kept.');
        $this->assertArrayHasKey('@humhub/views', $theme->pathMap, 'Default @humhub/views mapping must be added.');
        $this->assertContains(
            $this->themeBase . '/views',
            $theme->pathMap['@humhub/views'],
            'The theme view directory must be part of the default mapping.',
        );
    }

    public function testInitPathMapAddsDefaultMappingWhenNoUserConfig()
    {
        $theme = $this->makeTheme();

        $theme->applyTo($this->tmpDir . '/core/auth/login.php');

        $this->assertSame(
            ['@humhub/views' => [$this->themeBase . '/views']],
            $theme->pathMap,
        );
    }

    public function testInitPathMapRunsOnlyOnce()
    {
        $theme = $this->makeTheme();

        $initPathMap = new ReflectionMethod(Theme::class, 'initPathMap');
        $initPathMap->setAccessible(true);
        $initPathMap->invoke($theme);
        $initPathMap->invoke($theme);

        // A second run must not append the theme view directory again.
        $this->assertSame(
            [$this->themeBase . '/views'],
            $theme->pathMap['@humhub/views'],
        );
    }

    public function testCoreViewMappingStillResolvesThemedFile()
    {
        // Regression guard: the @humhub/views mapping (formerly handled by
        // parent::applyTo()) must keep resolving core views to the theme.
        $themedView = $this->themeBase . '/views/error/index.php';
        FileHelper::createDirectory(dirname($themedView));
        file_put_contents($themedView, '<?php // themed error view');

        $theme = $this->makeTheme();

        $resolved = $theme->applyTo(Yii::getAlias('@humhub/views/error/index.php'));
        $this->assertSame(FileHelper::normalizePath($themedView), FileHelper::normalizePath($resolved));
    }

    // -- ThemeLoader::mergeConfiguredPathMap() --------------------------------

    private function invokeMergeConfiguredPathMap(?BaseTheme $configured, BaseTheme $active): void
    {
        $method = new ReflectionMethod(ThemeLoader::class, 'mergeConfiguredPathMap');
        $method->setAccessible(true);
        $method->invoke(null, $configured, $active);
    }

    public function testMergeConfiguredPathMapCarriesEntriesToActiveTheme()
    {
        $configured = new BaseTheme(['pathMap' => ['@humhub/x/a.php' => '/override/a.php']]);
        $active = new BaseTheme();

        $this->invokeMergeConfiguredPathMap($configured, $active);

        $this->assertSame(['@humhub/x/a.php' => '/override/a.php'], $active->pathMap);
    }

    public function testMergeConfiguredPathMapMergesWithExistingActiveEntries()
    {
        $configured = new BaseTheme(['pathMap' => ['@humhub/x/a.php' => '/override/a.php']]);
        $active = new BaseTheme(['pathMap' => ['@humhub/x/b.php' => '/override/b.php']]);

        $this->invokeMergeConfiguredPathMap($configured, $active);

        $this->assertSame('/override/a.php', $active->pathMap['@humhub/x/a.php']);
        $this->assertSame('/override/b.php', $active->pathMap['@humhub/x/b.php']);
    }

    public function testMergeConfiguredPathMapIsNoOpWithoutConfiguredTheme()
    {
        $active = new BaseTheme(['pathMap' => ['@humhub/x/b.php' => '/override/b.php']]);

        $this->invokeMergeConfiguredPathMap(null, $active);

        $this->assertSame(['@humhub/x/b.php' => '/override/b.php'], $active->pathMap);
    }

    public function testMergeConfiguredPathMapIsNoOpWithEmptyConfiguredPathMap()
    {
        $configured = new BaseTheme();
        $active = new BaseTheme(['pathMap' => ['@humhub/x/b.php' => '/override/b.php']]);

        $this->invokeMergeConfiguredPathMap($configured, $active);

        $this->assertSame(['@humhub/x/b.php' => '/override/b.php'], $active->pathMap);
    }
}
