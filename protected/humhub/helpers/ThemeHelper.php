<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\helpers;

use Exception;
use humhub\components\Theme;
use humhub\modules\admin\models\forms\ThemeSettingsForm;
use RuntimeException;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use ScssPhp\ScssPhp\OutputStyle;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * ThemeHelper
 *
 * @since 1.3
 */
class ThemeHelper
{
    /**
     * Cascade layers of the compiled `theme.css`: the theme wins against the core whatever the specificity of
     * its selectors. Bootstrap and the core styles share the `core` layer, so specificity decides between them
     * as before. Outside of the layers, and therefore winning against them: the Bootstrap utilities and the
     * core's `_utilities.scss` and `_vendor.scss` (at the end of the `theme.css`), the custom SCSS of the Design
     * settings, the CSS of third-party libraries and of modules.
     *
     * Beware of `!important`, for which the order is reversed: an `!important` of the core wins against an
     * `!important` of the theme or of a module.
     *
     * @since 1.20
     */
    public const CSS_LAYERS = ['core', 'theme'];

    /**
     * The color variables renamed in 1.20, former name => new name. A theme that sets a former name sets the new
     * one, and reading a former name reads the new one (`Theme::variable()`). See `variables-deprecated.scss`.
     *
     * @since 1.20
     * @deprecated since 1.20, for the transition of themes and modules
     */
    public const DEPRECATED_VARIABLES = [
        'text-color-main' => 'body-color',
        'text-color-highlight' => 'body-emphasis-color',
        'text-color-secondary' => 'body-secondary-color',
        'text-color-soft2' => 'body-tertiary-color',
        'background-color-main' => 'body-bg',
        'background-color-secondary' => 'body-tertiary-bg',
        'background-color-page' => 'hh-page-bg',
        'background-color-highlight-soft' => 'hh-accent-bg-soft',
        'background3' => 'hh-border-color-strong',
        'text-color-main-dark' => 'body-color-dark',
        'text-color-highlight-dark' => 'body-emphasis-color-dark',
        'text-color-secondary-dark' => 'body-secondary-color-dark',
        'text-color-soft2-dark' => 'body-tertiary-color-dark',
        'background-color-main-dark' => 'body-bg-dark',
        'background-color-secondary-dark' => 'body-tertiary-bg-dark',
        'background-color-page-dark' => 'hh-page-bg-dark',
        'background-color-highlight-soft-dark' => 'hh-accent-bg-soft-dark',
        'background3-dark' => 'hh-border-color-strong-dark',
        'text-color-accent-contrast' => 'hh-accent-contrast',
        'text-color-primary-contrast' => 'hh-primary-contrast',
        'text-color-secondary-contrast' => 'hh-secondary-contrast',
        'text-color-info-contrast' => 'hh-info-contrast',
        'text-color-success-contrast' => 'hh-success-contrast',
        'text-color-warning-contrast' => 'hh-warning-contrast',
        'text-color-danger-contrast' => 'hh-danger-contrast',
        'text-color-light-contrast' => 'hh-light-contrast',
        'text-color-dark-contrast' => 'hh-dark-contrast',
    ];

    /**
     * @var Theme[] loaded themes
     */
    protected static $_themes = null;

    /**
     * Returns an array of all available themes.
     *
     * @return Theme[] The themes
     */
    public static function getThemes()
    {
        if (static::$_themes !== null) {
            return static::$_themes;
        }

        // Keyed by name, first one wins: a leftover webroot `themes/HumHub` (#8102) must not
        // shadow the core theme wherever it is resolved by name, e.g. in the parent lookup.
        $themes = self::getThemesByPath(Yii::getAlias('@humhub/themes'))
            + self::getThemesByPath(Yii::getAlias('@themes'));

        // Collect themes provided by modules
        foreach (Yii::$app->getModules() as $id => $module) {
            if (is_array($module) || is_string($module)) {
                try {
                    $module = Yii::$app->getModule($id);
                } catch (Exception $ex) {
                    Yii::error(
                        'Could not load module to fetch themes! Module: ' . $id . ' Error: ' . $ex->getMessage(),
                        'ui',
                    );
                    continue;
                }
            }

            $moduleThemePath = $module->getBasePath() . DIRECTORY_SEPARATOR . 'themes';
            if (is_dir($moduleThemePath)) {
                $themes += self::getThemesByPath($moduleThemePath);
            }
        }

        static::$_themes = $themes;
        return $themes;
    }

    /**
     * Returns an array of Theme instances of a given directory
     *
     * @param string $path the theme directory
     * @param array $additionalOptions options for Theme instance
     * @return Theme[]
     */
    public static function getThemesByPath($path, $additionalOptions = [])
    {
        $themes = [];
        $path = realpath($path);
        foreach (scandir($path) as $file) {
            if ($file === '.' || $file === '..' || !is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                continue;
            }

            $theme = static::getThemeByPath($path . DIRECTORY_SEPARATOR . $file, $additionalOptions);
            if ($theme !== null && ($theme->isActive() || !str_ends_with($theme->name, '.bs3.old'))) {
                $themes[$theme->name] = $theme;
            }
        }
        return $themes;
    }


    /**
     * Return a Theme instance by given path
     *
     * @param $path
     * @param array $options additional options for Theme instance
     * @return Theme|null
     */
    public static function getThemeByPath($path, $options = [])
    {
        try {
            /** @var Theme $theme */
            $theme = Yii::createObject(ArrayHelper::merge([
                'class' => Theme::class,
                'basePath' => $path,
                'name' => basename((string)$path),
            ], $options));
        } catch (InvalidConfigException $e) {
            Yii::error('Could not get theme by path "' . $path . '" - Error: ' . $e->getMessage());
            return null;
        }

        // A theme directory without its SCSS variables file is not a usable theme
        // (e.g. an empty `themes/HumHub` skeleton left behind by an update that moved
        // the theme into `protected/humhub`). Loading it would break the SCSS build and,
        // worse, shadow the real core theme in getThemes() since both share the name.
        if (!is_file(ScssHelper::getVariableFile($theme))) {
            Yii::warning('Ignoring invalid theme without SCSS variables file: ' . $path, 'ui');
            return null;
        }

        return $theme;
    }


    /**
     * Returns a Theme by given name
     *
     * @param string $name of the theme
     * @return Theme
     */
    public static function getThemeByName($name)
    {
        foreach (self::getThemes() as $theme) {
            if ($theme->name === $name) {
                return $theme;
            }
        }

        return null;
    }


    /**
     * @param Theme $theme
     * @param bool $includeCustomScss whether the variables of the Custom SCSS overwrite the ones of the theme files
     * @return array
     * @throws SassException if syntax error in the custom SCSS, only when $includeCustomScss is true
     * @throws RuntimeException if the custom SCSS is malformed, only when $includeCustomScss is true -
     *                          reading without it is free of both, which is the point of the parameter
     */
    public static function getAllVariables(Theme $theme, bool $includeCustomScss = true): array
    {
        // Get variables from theme files
        $variables = ScssHelper::getVariables(Yii::getAlias('@humhub/resources/scss/variables.scss'));
        foreach (array_reverse(static::getThemeTree($theme)) as $treeTheme) {
            $variables = ArrayHelper::merge(
                $variables,
                static::renameDeprecatedVariables(ScssHelper::getVariables(ScssHelper::getVariableFile($treeTheme))),
            );
        }

        if ($includeCustomScss) {
            // Overwrite with the variables of the Custom SCSS (Administration → Appearance)
            [$customVariables, $customMaps, $otherCustomScss] = ScssHelper::extractVariablesAndMaps(
                Yii::$app->settings->get('themeCustomScss'),
            );
            $variables = ArrayHelper::merge(
                $variables,
                static::renameDeprecatedVariables(ScssHelper::parseVariables($customVariables)),
            );
        }

        return ScssHelper::updateLinkedScssVariables($variables);
    }

    /**
     * Sets the new name of a color variable renamed in 1.20 that a theme sets by its former name, unless the theme
     * sets the new name too (see `variables-deprecated.scss`)
     *
     * @param array $variables the variables of one theme file, or of the custom SCSS
     * @return array
     * @since 1.20
     */
    protected static function renameDeprecatedVariables(array $variables): array
    {
        foreach (static::DEPRECATED_VARIABLES as $former => $new) {
            if (isset($variables[$former]) && !isset($variables[$new])) {
                $variables[$new] = $variables[$former];
            }
        }

        return $variables;
    }


    /**
     * Returns an array of all used themes
     *
     * @param Theme $theme
     * @param bool $includeBaseTheme should the given theme also included in the theme tree
     * @return Theme[] the parent themes
     */
    public static function getThemeTree(Theme $theme, $includeBaseTheme = true)
    {
        if (!$includeBaseTheme) {
            $theme = static::getThemeParent($theme);
            if ($theme === null) {
                return [];
            }
        }

        $parents = [];

        do {
            // check loop
            if (array_key_exists($theme->name, $parents)) {
                Yii::error('Theme parent loop detected: ' . $theme->name, 'ui');
                return $parents;
            }
            $parents[$theme->name] = $theme;
            $theme = static::getThemeParent($theme);
        } while ($theme !== null);

        return $parents;
    }

    /**
     * @param Theme $theme
     * @return Theme|null
     */
    public static function getThemeParent(Theme $theme)
    {
        $themes = static::getThemes();

        $baseTheme = ScssHelper::getVariable(
            ScssHelper::getVariableFile($theme),
            'baseTheme',
        );

        if ($baseTheme && isset($themes[$baseTheme]) && $baseTheme !== $theme->name) {
            return $themes[$baseTheme];
        }

        return null;
    }


    /**
     * @param Theme|null $theme
     * @return bool
     * @since 1.4
     */
    public static function isFluid(?Theme $theme = null)
    {
        if ($theme === null) {
            $theme = Yii::$app->view->theme;
        }

        return $theme->variable('isFluid') == 'true';
    }

    /**
     * Imports Bootstrap, the core and the theme stylesheets into the cascade layers of [[CSS_LAYERS]]
     *
     * @param string[] $themeBuildFiles the `build.scss` of each theme, parent theme first
     * @return string
     * @since 1.20
     */
    protected static function getLayersScss(array $themeBuildFiles): string
    {
        $bootstrapPath = Yii::getAlias('@vendor/twbs/bootstrap/scss');

        // Bootstrap's own entry file, split into the files without CSS output (functions, variables, mixins, the
        // utilities map), its components and helpers, and its utilities. The first are imported outside of the
        // layers: mixins and functions of an import nested in a block are only defined within that block.
        preg_match_all('/^@import "([^"]+)";/m', file_get_contents($bootstrapPath . '/bootstrap.scss'), $matches);
        $global = $components = $utilities = [];
        foreach ($matches[1] as $file) {
            $path = $bootstrapPath . '/' . $file;
            if ($file === 'mixins/banner') {
                continue;
            }
            if (in_array($file, ['functions', 'variables', 'variables-dark', 'maps', 'mixins', 'utilities'], true)) {
                $global[] = $path;
            } elseif ($file === 'utilities/api') {
                $utilities[] = $path;
            } else {
                $components[] = $path;
            }
        }
        $global[] = Yii::getAlias('@humhub/resources/scss/mixins');

        $layers = [
            'core' => [...$components, Yii::getAlias('@humhub/resources/scss/build')],
            'theme' => $themeBuildFiles,
        ];

        $scss = '@import "' . implode('", "', $global) . '";' . PHP_EOL
            . '@layer ' . implode(', ', self::CSS_LAYERS) . ';' . PHP_EOL;
        foreach ($layers as $layer => $files) {
            if ($files !== []) {
                $scss .= '@layer ' . $layer . ' { @import "' . implode('", "', $files) . '"; }' . PHP_EOL;
            }
        }

        // Outside of the layers: the utilities (all `!important`) keep winning against an `!important` of a module,
        // the styles for the third-party libraries against their CSS, which is not in a layer either
        $unlayered = [
            ...$utilities,
            Yii::getAlias('@humhub/resources/scss/utilities'),
            Yii::getAlias('@humhub/resources/scss/vendor'),
        ];

        return $scss . '@import "' . implode('", "', $unlayered) . '";' . PHP_EOL;
    }

    /**
     * @param Theme|null $theme
     * @return true|string true if successfully compiled, string if error occurred
     * @throws \yii\base\Exception
     */
    public static function buildCss(?Theme $theme = null, bool $includeCustomScss = true): bool|string
    {
        $theme ??= Yii::$app->view->theme;
        $treeThemes = static::getThemeTree($theme);
        $compiler = new Compiler();

        try {
            [$customVariables, $customMaps, $otherCustomScss] = $includeCustomScss
                ? ScssHelper::extractVariablesAndMaps((string)Yii::$app->settings->get('themeCustomScss'))
                : ['', '', ''];
        } catch (SassException|RuntimeException $e) {
            return static::logAndGetError('Error while compiling the custom SCSS code: ' . $e->getMessage());
        }

        // Compress CSS
        $compiler->setOutputStyle(OutputStyle::COMPRESSED);

        // Set import paths
        $compiler->setImportPaths(Yii::getAlias('@vendor/twbs/bootstrap/scss'));
        $compiler->addImportPath(Yii::getAlias('@humhub/resources/scss'));
        foreach ($treeThemes as $treeTheme) {
            $compiler->addImportPath($treeTheme->getBasePath() . '/scss');
        }

        // Import Bootstrap functions
        $imports[] = Yii::getAlias('@vendor/twbs/bootstrap/scss/functions');

        // Import variables child theme first, because they have the !default flag
        foreach ($treeThemes as $treeTheme) {
            $variablesFile = $treeTheme->getBasePath() . DIRECTORY_SEPARATOR . 'scss' . DIRECTORY_SEPARATOR . 'variables';
            if (file_exists($variablesFile . '.scss')) {
                $imports[] = $variablesFile;
            }
        }
        $imports[] = Yii::getAlias('@humhub/resources/scss/variables-deprecated');
        $imports[] = Yii::getAlias('@humhub/resources/scss/variables');
        $imports[] = Yii::getAlias('@vendor/twbs/bootstrap/scss/variables');

        // Import maps
        $imports[] = Yii::getAlias('@vendor/twbs/bootstrap/scss/maps');
        $imports[] = Yii::getAlias('@humhub/resources/scss/maps');

        // Bootstrap, the core and the theme stylesheets are compiled into cascade layers, parent theme first
        $themeBuildFiles = [];
        foreach (array_reverse($treeThemes) as $treeTheme) {
            $buildFile = $treeTheme->getBasePath() . DIRECTORY_SEPARATOR . 'scss' . DIRECTORY_SEPARATOR . 'build';
            if (file_exists($buildFile . '.scss')) {
                $themeBuildFiles[] = $buildFile;
            }
        }

        // Set source map
        // TODO improve the source map to deal with multiple import paths
        $compiler->setSourceMap(Compiler::SOURCE_MAP_FILE);
        $compiler->setSourceMapOptions([
            'sourceMapURL' => './theme.map',
            'sourceMapFilename' => 'theme.css',
            'sourceRoot' => $theme->name === Theme::CORE_THEME_NAME ? '../resources/scss/' : '../',
            'sourceMapBasepath' => $theme->name === Theme::CORE_THEME_NAME ? Yii::getAlias(
                '@humhub/resources/scss',
            ) : $theme->getBasePath(),
        ]);

        // Define the output files
        $cssDir = $theme->getPublishedResourcesPath() . DIRECTORY_SEPARATOR . 'css';
        $cssFilePath = $cssDir . DIRECTORY_SEPARATOR . 'theme.css';
        $mapFilePath = $cssDir . DIRECTORY_SEPARATOR . 'theme.map';

        // The colours of Administration → Appearance → Theme
        $scssSource = '';
        foreach (ThemeSettingsForm::COLORS as $color) {
            $value = Yii::$app->settings->get(ThemeSettingsForm::colorAttribute($color));
            if ($value && !Yii::$app->settings->get(ThemeSettingsForm::defaultAttribute($color), true)) {
                $scssSource .= '$' . $color . ': ' . $value . ';' . PHP_EOL;
            }
        }
        $scssSource
            .= $customVariables . PHP_EOL
            . $customMaps . PHP_EOL
            . '@import "' . implode('", "', $imports) . '";' . PHP_EOL
            . static::getLayersScss($themeBuildFiles)
            // Not in a layer: the Custom SCSS (Administration → Appearance) wins against all of the above
            . $otherCustomScss;

        // Compile to CSS
        try {
            $result = $compiler->compileString(
                str_replace('\\', '/', $scssSource),
            ); // replace backslashes with forward slashes for Windows compatibility
            $css = $result->getCss();
            $map = $result->getSourceMap();
            if (!$css) {
                return static::logAndGetError('Could not compile SCSS');
            }
            Yii::$app->assetManager->fileWrite($cssFilePath, $css);
            Yii::$app->assetManager->fileWrite($mapFilePath, $map);
        } catch (SassException $e) {
            return static::logAndGetError('Saas compiler error: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * @param string $filePath
     * @return string|null null if no error, otherwise a string with the error message
     */
    private static function getFilePermissionError(string $filePath): ?string
    {
        if (file_exists($filePath) && !is_writable($filePath)) {
            return 'File ' . $filePath . ' is not writable. Check files ownership and permissions. Current: ' . substr(
                sprintf('%o', fileperms($filePath)),
                -4,
            );
        }

        return null;
    }

    private static function logAndGetError(string $errorMsg): string
    {
        $fullErrorMsg = 'Error while building the CSS from theme SCSS.' . ' ' . $errorMsg;
        Yii::error($fullErrorMsg);
        return $fullErrorMsg;
    }
}
