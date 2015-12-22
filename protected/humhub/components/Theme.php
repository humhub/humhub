<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;
use yii\helpers\FileHelper;
use humhub\models\Setting;

/**
 * @inheritdoc
 */
class Theme extends \yii\base\Theme
{

    /**
     * Name of the Theme
     * 
     * @var string
     */
    public $name;

    /**
     * @inheritdoc
     */
    private $_baseUrl = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->getBasePath() == '') {
            $this->setBasePath('@webroot/themes/' . $this->name);
        }

        $this->pathMap = [
            '@humhub/views' => $this->getBasePath() . '/views',
        ];
        parent::init();
    }

    /**
     * @return string the base URL (without ending slash) for this theme. All resources of this theme are considered
     * to be under this base URL.
     */
    public function getBaseUrl()
    {

        if ($this->_baseUrl !== null) {
            return $this->_baseUrl;
        }

        $this->_baseUrl = rtrim(Yii::getAlias('@web/themes/' . $this->name), '/');
        return $this->_baseUrl;
    }

    /**
     * @inheritdoc
     */
    public function applyTo($path)
    {

        $autoPath = $this->autoFindModuleView($path);
        if ($autoPath !== null && file_exists($autoPath)) {
            return $autoPath;
        }

        // Web Resource e.g. image
        if (substr($path, 0, 5) === '@web/') {
            $themedFile = str_replace('@web', $this->getBasePath(), $path);
            if (file_exists($themedFile)) {
                return str_replace('@web', $this->getBaseUrl(), $path);
            } else {
                return $path;
            }
        }

        return parent::applyTo($path);
    }

    /**
     * Tries to automatically maps the view file of a module to a themed one.
     * 
     * Formats:

     *   .../moduleId/views/controllerId/viewName.php
     *   to:
     *   .../views/moduleId/controllerId/viewName.php
     *
     *   .../moduleId/[widgets|activities|notifications]/views/viewName.php
     *   to:
     *   .../views/moduleId/[widgets|activities|notifications]/viewName.php
     * 
     * @return string theme view path or null
     */
    protected function autoFindModuleView($path)
    {
        $sep = preg_quote(DIRECTORY_SEPARATOR);
        $path = FileHelper::normalizePath($path);

        // .../moduleId/views/controllerId/viewName.php
        if (preg_match('@.*' . $sep . '(.*?)' . $sep . 'views' . $sep . '(.*?)' . $sep . '(.*?)\.php$@', $path, $hits)) {
            return $this->getBasePath() . '/views/' . $hits[1] . '/' . $hits[2] . '/' . $hits[3] . '.php';
        }

        // /moduleId/[widgets|activities|notifications]/views/viewName.php
        if (preg_match('@.*' . $sep . '(.*?)' . $sep . '(widgets|notifications|activities)' . $sep . 'views' . $sep . '(.*?)\.php$@', $path, $hits)) {
            // Handle special case (protected/humhub/widgets/views/view.php => views/widgets/view.php
            if ($hits[1] == 'humhub') {
                return $this->getBasePath() . '/views/' . $hits[2] . '/' . $hits[3] . '.php';
            }
            return $this->getBasePath() . '/views/' . $hits[1] . '/' . $hits[2] . '/' . $hits[3] . '.php';
        }

        return null;
    }

    /**
     * Returns an array of all installed themes.
     *
     * @return Array Theme instances
     */
    public static function getThemes()
    {
        $themes = array();
        $themePaths = [];
        $themePaths[] = \Yii::getAlias('@webroot/themes');

        // Collect enabled module theme paths
        foreach (Yii::$app->getModules() as $module) {
            $basePath = "";
            if (is_array($module)) {
                if (isset($module['class'])) {
                    $reflector = new \ReflectionClass($module['class']);
                    $basePath = dirname($reflector->getFileName());
                }
            } else {
                $basePath = $module->getBasePath();
            }

            if (is_dir($basePath . DIRECTORY_SEPARATOR . 'themes')) {
                $themePaths[] = $basePath . DIRECTORY_SEPARATOR . 'themes';
            }
        }

        foreach ($themePaths as $themePath) {
            foreach (scandir($themePath) as $file) {
                if ($file == "." || $file == ".." || !is_dir($themePath . DIRECTORY_SEPARATOR . $file)) {
                    continue;
                }
                $themes[] = Yii::createObject([
                            'class' => 'humhub\components\Theme',
                            'basePath' => $themePath . DIRECTORY_SEPARATOR . $file,
                            'name' => $file
                ]);
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

        return [
            'components' => [
                'view' => [
                    'theme' => [
                        'name' => $theme->name,
                        'basePath' => $theme->getBasePath()
                    ],
                ],
                'mailer' => [
                    'view' => [
                        'theme' => [
                            'name' => $theme->name,
                            'basePath' => $theme->getBasePath()
                        ]
                    ]
                ]
            ]
        ];
    }

    public static function setColorVariables($themeName)
    {
        $theme = self::getThemeByName($themeName);

        if ($theme === null) {
            return;
        }

        $lessFileName = Yii::getAlias($theme->getBasePath() . '/css/theme.less');
        if (file_exists($lessFileName)) {
            $file = fopen($lessFileName, "r") or die("Unable to open file!");
            $less = fread($file, filesize($lessFileName));
            fclose($file);

            $startDefault = strpos($less, '@default') + 10;
            $startPrimary = strpos($less, '@primary') + 10;
            $startInfo = strpos($less, '@info') + 7;
            $startSuccess = strpos($less, '@success') + 10;
            $startWarning = strpos($less, '@warning') + 10;
            $startDanger = strpos($less, '@danger') + 9;
            $length = 7;

            Setting::Set('colorDefault', substr($less, $startDefault, $length));
            Setting::Set('colorPrimary', substr($less, $startPrimary, $length));
            Setting::Set('colorInfo', substr($less, $startInfo, $length));
            Setting::Set('colorSuccess', substr($less, $startSuccess, $length));
            Setting::Set('colorWarning', substr($less, $startWarning, $length));
            Setting::Set('colorDanger', substr($less, $startDanger, $length));
        }
    }

}
