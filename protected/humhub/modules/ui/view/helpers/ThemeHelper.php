<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\view\helpers;

use humhub\modules\ui\view\components\Theme;
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
            if (is_array($module)) {
                $module = Yii::$app->getModule($id);
            }

            $moduleThemePath = $module->getBasePath() . DIRECTORY_SEPARATOR . 'themes';
            if (is_dir($moduleThemePath)) {
                $themes = ArrayHelper::merge(
                    $themes,
                    self::getThemesByPath(
                        $moduleThemePath, ['publishResources' => true]
                    )
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
                'class' => 'humhub\modules\ui\view\components\Theme',
                'basePath' => $path,
                'name' => basename($path),
                'publishResources' => (dirname($path) !== Yii::getAlias('@themes'))
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
     */
    public static function getAllVariables(Theme $theme)
    {
        $variables = LessHelper::parseLessVariables(Yii::getAlias('@webroot-static/less/variables.less'));
        foreach (array_reverse(static::getThemeTree($theme)) as $theme) {
            $eeVariablesFile = $theme->getBasePath() . '/less/enterprise_variables.less';
            if (file_exists($eeVariablesFile)) {
                $variables = ArrayHelper::merge($variables, LessHelper::parseLessVariables($eeVariablesFile));
            }

            $variables = ArrayHelper::merge($variables, LessHelper::parseLessVariables(LessHelper::getVariableFile($theme)));
        }

        return $variables;
    }


    /**
     * Returns an array of all used themes
     *
     * @param Theme $theme
     * @param boolean $includeBaseTheme should the given theme also included in the theme tree
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

        $variables = LessHelper::parseLessVariables(
            LessHelper::getVariableFile($theme)
        );

        if (isset($variables['baseTheme']) && isset($themes[$variables['baseTheme']]) && $variables['baseTheme'] !== $theme->name) {
            return $themes[$variables['baseTheme']];
        }

        return null;
    }


    public static function isFluid(Theme $theme = null)
    {
        if ($theme === null) {
            $theme = Yii::$app->view->theme;
        }

        return !empty($theme->variable('isFluid'));
    }

}
