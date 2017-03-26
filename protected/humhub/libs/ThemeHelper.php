<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use yii\helpers\ArrayHelper;
use humhub\components\Theme;

/**
 * ThemeHelper
 *
 * @since 1.1
 * @author Luke
 */
class ThemeHelper
{

    /**
     * Returns an array of all available themes.
     *
     * @return Array Theme instances
     */
    public static function getThemes()
    {
        $themes = self::getThemesByPath(Yii::getAlias('@webroot/themes'));

        // Collect themes provided by modules
        foreach (Yii::$app->getModules() as $id => $module) {
            if (is_array($module)) {
                $module = Yii::$app->getModule($id);
            }

            $moduleThemePath = $module->getBasePath() . DIRECTORY_SEPARATOR . 'themes';
            if (is_dir($moduleThemePath)) {
                $themes = ArrayHelper::merge($themes, self::getThemesByPath($moduleThemePath, ['publishResources' => true]));
            }
        }

        return $themes;
    }

    /**
     * Returns an array of Theme instances of a given directory
     *
     * @param string $path the theme directory
     * @param type $additionalOptions options for Theme instance
     * @return Theme[]
     */
    public static function getThemesByPath($path, $additionalOptions = [])
    {
        $themes = [];
        if (is_dir($path)) {
            foreach (scandir($path) as $file) {

                // Skip dots and non directories
                if ($file == "." || $file == ".." || !is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                    continue;
                }

                $themes[] = Yii::createObject(ArrayHelper::merge([
                    'class' => 'humhub\components\Theme',
                    'basePath' => $path . DIRECTORY_SEPARATOR . $file,
                    'name' => $file
                ], $additionalOptions));
            }
        }

        return $themes;
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
     * Returns configuration array of given theme
     *
     * @param Theme|string $theme name or theme instance
     * @return array Configuration
     */
    public static function getThemeConfig($theme)
    {
        if (is_string($theme)) {
            $theme = self::getThemeByName($theme);
        }

        if ($theme === null) {
            return [];
        }

        $theme->beforeActivate();

        return [
            'components' => [
                'view' => [
                    'theme' => [
                        'name' => $theme->name,
                        'basePath' => $theme->getBasePath(),
                        'publishResources' => $theme->publishResources,
                    ],
                ],
                'mailer' => [
                    'view' => [
                        'theme' => [
                            'name' => $theme->name,
                            'basePath' => $theme->getBasePath(),
                            'publishResources' => $theme->publishResources,
                        ]
                    ]
                ]
            ]
        ];
    }

}
