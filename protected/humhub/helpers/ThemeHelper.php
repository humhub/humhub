<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\helpers;

use Exception;
use humhub\components\Theme;
use humhub\modules\admin\models\forms\DesignSettingsForm;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use ScssPhp\ScssPhp\OutputStyle;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * ThemeHelper
 *
 * @since 1.3
 */
class ThemeHelper
{
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

        $themes = self::getThemesByPath(Yii::getAlias('@themes'));

        // Collect themes provided by modules
        foreach (Yii::$app->getModules() as $id => $module) {
            if (is_array($module) || is_string($module)) {
                try {
                    $module = Yii::$app->getModule($id);
                } catch (Exception $ex) {
                    Yii::error('Could not load module to fetch themes! Module: ' . $id . ' Error: ' . $ex->getMessage(), 'ui');
                    continue;
                }
            }

            $moduleThemePath = $module->getBasePath() . DIRECTORY_SEPARATOR . 'themes';
            if (is_dir($moduleThemePath)) {
                $themes = ArrayHelper::merge(
                    $themes,
                    self::getThemesByPath($moduleThemePath),
                );
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
            if ($file == "." || $file == ".." || !is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                continue;
            }

            $theme = static::getThemeByPath($path . DIRECTORY_SEPARATOR . $file, $additionalOptions);
            if ($theme !== null) {
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
                'name' => basename((string) $path),
            ], $options));
        } catch (InvalidConfigException $e) {
            Yii::error('Could not get theme by path "' . $path . '" - Error: ' . $e->getMessage());
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
     * @return array
     * @throws Exception
     */
    public static function getAllVariables(Theme $theme): array
    {
        $variables = ScssHelper::getVariables(Yii::getAlias('@webroot-static/scss/variables.scss'));
        foreach (array_reverse(static::getThemeTree($theme)) as $treeTheme) {
            $variables = ArrayHelper::merge($variables, ScssHelper::getVariables(ScssHelper::getVariableFile($treeTheme)));
        }

        return ScssHelper::updateLinkedScssVariables($variables);
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
     * @param Theme|null $theme
     * @return true|string true if successfully compiled, string if error occurred
     * @throws \yii\base\Exception
     */
    public static function buildCss(?Theme $theme = null): bool|string
    {
        $theme ??= Yii::$app->view->theme;
        $treeThemes = static::getThemeTree($theme);
        $compiler = new Compiler();
        $designSettingsForm = new DesignSettingsForm();

        // Compress CSS
        $compiler->setOutputStyle(OutputStyle::COMPRESSED);

        // Set import paths
        $compiler->setImportPaths(Yii::getAlias('@bower/bootstrap/scss'));
        $compiler->addImportPath(Yii::getAlias('@webroot-static/scss'));
        foreach ($treeThemes as $treeTheme) {
            $compiler->addImportPath($treeTheme->getBasePath() . '/scss');
        }

        // Import Bootstrap functions
        $imports[] = Yii::getAlias('@bower/bootstrap/scss/functions');

        // Import variables child theme first, because they have the !default flag
        foreach ($treeThemes as $treeTheme) {
            $imports[] = $treeTheme->getBasePath() . DIRECTORY_SEPARATOR . 'scss' . DIRECTORY_SEPARATOR . 'variables';
        }
        $imports[] = Yii::getAlias('@webroot-static/scss/variables');
        $imports[] = Yii::getAlias('@bower/bootstrap/scss/variables');

        // Import maps
        $imports[] = Yii::getAlias('@bower/bootstrap/scss/maps');
        $imports[] = Yii::getAlias('@webroot-static/scss/maps');

        // Import Bootstrap files
        $imports[] = Yii::getAlias('@bower/bootstrap/scss/bootstrap');

        // Import all other files, in reverse order (parent theme first)
        $imports[] = Yii::getAlias('@webroot-static/scss/build');
        foreach (array_reverse($treeThemes) as $treeTheme) {
            $imports[] = $treeTheme->getBasePath() . DIRECTORY_SEPARATOR . 'scss' . DIRECTORY_SEPARATOR . 'build';
        }

        // Set source map
        // TODO improve the source map to deal with multiple import paths
        $compiler->setSourceMap(Compiler::SOURCE_MAP_FILE);
        $compiler->setSourceMapOptions([
            'sourceMapURL' => './theme.map',
            'sourceMapFilename' => 'theme.css',
            'sourceRoot' => $theme->name === 'HumHub' ? '../../../static/scss/' : '../',
            'sourceMapBasepath' => $theme->name === 'HumHub' ? Yii::getAlias('@webroot-static/scss') : $theme->getBasePath(),
        ]);

        // Define the output files
        $cssDir = $theme->getPublishedResourcesPath() . DIRECTORY_SEPARATOR . 'css';
        if (!file_exists($cssDir) && !FileHelper::createDirectory($cssDir)) {
            return static::logAndGetError('Could not create directory ' . $cssDir);
        }
        $cssFilePath = $cssDir . DIRECTORY_SEPARATOR . 'theme.css';
        $mapFilePath = $cssDir . DIRECTORY_SEPARATOR . 'theme.map';

        // Check if files are writable
        $cssFilePermissionError = static::getFilePermissionError($cssFilePath);
        if ($cssFilePermissionError) {
            return static::logAndGetError($cssFilePermissionError);
        }
        $mapFilePermissionError = static::getFilePermissionError($mapFilePath);
        if ($mapFilePermissionError) {
            return static::logAndGetError($mapFilePermissionError);
        }

        // Create SCSS source from Design Settings form and imports
        $scssSource = '';
        if (!$designSettingsForm->useDefaultThemePrimaryColor && $designSettingsForm->themePrimaryColor) {
            $scssSource .= '$primary: ' . $designSettingsForm->themePrimaryColor . ';' . PHP_EOL;
        }
        if (!$designSettingsForm->useDefaultThemeAccentColor && $designSettingsForm->themeAccentColor) {
            $scssSource .= '$accent: ' . $designSettingsForm->themeAccentColor . ';' . PHP_EOL;
        }
        if (!$designSettingsForm->useDefaultThemeSecondaryColor && $designSettingsForm->themeSecondaryColor) {
            $scssSource .= '$secondary: ' . $designSettingsForm->themeSecondaryColor . ';' . PHP_EOL;
        }
        if (!$designSettingsForm->useDefaultThemeSuccessColor && $designSettingsForm->themeSuccessColor) {
            $scssSource .= '$success: ' . $designSettingsForm->themeSuccessColor . ';' . PHP_EOL;
        }
        if (!$designSettingsForm->useDefaultThemeDangerColor && $designSettingsForm->themeDangerColor) {
            $scssSource .= '$danger: ' . $designSettingsForm->themeDangerColor . ';' . PHP_EOL;
        }
        if (!$designSettingsForm->useDefaultThemeWarningColor && $designSettingsForm->themeWarningColor) {
            $scssSource .= '$warning: ' . $designSettingsForm->themeWarningColor . ';' . PHP_EOL;
        }
        if (!$designSettingsForm->useDefaultThemeInfoColor && $designSettingsForm->themeInfoColor) {
            $scssSource .= '$info: ' . $designSettingsForm->themeInfoColor . ';' . PHP_EOL;
        }
        if (!$designSettingsForm->useDefaultThemeLightColor && $designSettingsForm->themeLightColor) {
            $scssSource .= '$light: ' . $designSettingsForm->themeLightColor . ';' . PHP_EOL;
        }
        if (!$designSettingsForm->useDefaultThemeDarkColor && $designSettingsForm->themeDarkColor) {
            $scssSource .= '$dark: ' . $designSettingsForm->themeDarkColor . ';' . PHP_EOL;
        }
        $scssSource
            .= '@import "' . implode('", "', $imports) . '";' . PHP_EOL
            . $designSettingsForm->themeCustomScss;

        // Compile to CSS
        try {
            $result = $compiler->compileString(str_replace('\\', '/', $scssSource)); // replace backslashes with forward slashes for Windows compatibility
            $css = $result->getCss();
            $map = $result->getSourceMap();
            if (!$css) {
                return static::logAndGetError('Could not compile SCSS');
            }
            if (file_put_contents($cssFilePath, $css) === false) {
                return static::logAndGetError('Could not write to file ' . $cssFilePath);
            }
            if (file_put_contents($mapFilePath, $map) === false) {
                return static::logAndGetError('Could not write to file ' . $mapFilePath);
            }
        } catch (SassException $e) {
            return static::logAndGetError($e->getMessage());
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
            return 'File ' . $filePath . ' is not writable. Check files ownership and permissions. Current: ' . substr(sprintf('%o', fileperms($filePath)), -4);
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
