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
                    self::getThemesByPath(
                        $moduleThemePath,
                        ['publishResources' => true],
                    ),
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
                'class' => 'humhub\components\Theme',
                'basePath' => $path,
                'name' => basename($path),
                'publishResources' => (dirname($path) !== Yii::getAlias('@themes')),
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
    public static function isFluid(Theme $theme = null)
    {
        if ($theme === null) {
            $theme = Yii::$app->view->theme;
        }

        return $theme->variable('isFluid') == 'true';
    }

    public static function buildCss(?Theme $theme = null): bool|string
    {
        $theme = $theme ?? Yii::$app->view->theme;
        $treeThemes = array_reverse(static::getThemeTree($theme));
        $compiler = new Compiler();
        $designSettingsForm = new DesignSettingsForm();
        $variableImports = [];
        $otherImports = [];

        // Set import paths
        $compiler->setImportPaths(Yii::getAlias('@bower/bootstrap/scss'));
        $compiler->addImportPath(Yii::getAlias('@webroot-static/scss'));
        foreach ($treeThemes as $treeTheme) {
            $compiler->addImportPath($treeTheme->getBasePath() . '/scss');
        }

        // Import variables (bootstrap variables have a !default suffix to allow overwriting)
        $variableImports[] = Yii::getAlias('@webroot-static/scss/variables');
        foreach ($treeThemes as $treeTheme) {
            $variableImports[] = $treeTheme->getBasePath() . '/scss/variables';
        }

        // Import all other files
        $otherImports[] = Yii::getAlias('@bower/bootstrap/scss/bootstrap'); // includes the variables.scss file
        $otherImports[] = Yii::getAlias('@webroot-static/scss/humhub');
        foreach ($treeThemes as $treeTheme) {
            $otherImports[] = $treeTheme->getBasePath() . '/scss/build';
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
        $cssFilePath = $theme->getBasePath() . '/css/theme.css';
        $mapFilePath = $theme->getBasePath() . '/css/theme.map';
        $errorMsgStart = Yii::t('UiModule.base', 'Cannot compile SCSS to CSS code.');

        // Check if files are writable
        if ($cssFilePermissionError = static::getFilePermissionError($cssFilePath)) {
            return $errorMsgStart . ' ' . $cssFilePermissionError;
        }
        if ($mapFilePermissionError = static::getFilePermissionError($mapFilePath)) {
            return $errorMsgStart . ' ' . $mapFilePermissionError;
        }

        // Create SCSS source from imports and Design Settings form
        $scssSource = '@import "' . implode('", "', $variableImports) . '";' . PHP_EOL;
        if ($designSettingsForm->themePrimaryColor) {
            $scssSource .= '$primary: ' . $designSettingsForm->themePrimaryColor . ';' . PHP_EOL;
        }
        if ($designSettingsForm->themeSecondaryColor) {
            $scssSource .= '$secondary: ' . $designSettingsForm->themeSecondaryColor . ';' . PHP_EOL;
        }
        $scssSource .=
            '@import "' . implode('", "', $otherImports) . '";' . PHP_EOL .
            $designSettingsForm->themeCustomScss;

        // Compile to CSS
        try {
            $result = $compiler->compileString($scssSource);
            if (
                file_put_contents($cssFilePath, $result->getCss()) !== false
                && file_put_contents($mapFilePath, $result->getSourceMap()) !== false
            ) {
                Yii::$app->assetManager->clear();
                return true;
            }
        } catch (SassException $e) {
            $errorMsg = $e->getMessage();
        }

        return $errorMsgStart . (!empty($errorMsg) ? ' ' . $errorMsg : '');
    }

    private static function getFilePermissionError($filePath): ?string
    {
        if (file_exists($filePath) && !is_writable($filePath)) {
            return
                Yii::t('UiModule.base', 'File {filePath} is not writable. Check files ownership and permissions. Current: {currentPermissions}', [
                    'filePath' => $filePath,
                    'currentPermissions' => substr(sprintf('%o', fileperms($filePath)), -4),
                ]);
        }

        return null;
    }
}
