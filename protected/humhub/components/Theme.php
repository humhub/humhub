<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;
use yii\helpers\FileHelper;
use humhub\libs\ThemeHelper;

/**
 * @inheritdoc
 */
class Theme extends \yii\base\Theme
{

    const VARIABLES_CACHE_ID = 'theme_variables';

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
     * Indicates that resources should be published via assetManager
     */
    public $publishResources = false;

    /**
     * @var array theme variables loaded from the themes variables.less merged with default variables.less file.
     */
    private $_variables;

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
     * @inheritdoc
     */
    public function getBaseUrl()
    {
        if ($this->_baseUrl !== null) {
            return $this->_baseUrl;
        }

        $this->_baseUrl = ($this->publishResources) ? $this->publishResources() : rtrim(Yii::getAlias('@web/themes/' . $this->name), '/');
        return $this->_baseUrl;
    }

    /**
     * This method will be called before when this theme is written to the
     * dynamic configuration file.
     */
    public function beforeActivate()
    {
        // Force republish theme files
        $this->publishResources(true);

        // Store color variables to configuration
        $this->storeColorsToConfig();
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
         if (substr($path, 0, 5) === '@web/' || substr($path, 0, 12) === '@web-static/') {
            $themedFile = str_replace(['@web/', '@web-static/'], [$this->getBasePath(), $this->getBasePath() . DIRECTORY_SEPARATOR . 'static'], $path);
            // Check if file exists in theme base dir
            if (file_exists($themedFile)) {
                return str_replace(['@web/', '@web-static/'], [$this->getBaseUrl(), $this->getBaseUrl() . DIRECTORY_SEPARATOR . 'static'], $path);
            }
            return $path;
        }

        return parent::applyTo($path);
    }

    /**
     * Publishs theme assets (e.g. images or css)
     *
     * @param boolean|null $force
     * @return string url of published resources
     */
    public function publishResources($force = null)
    {
        if ($force === null) {
            $force = (YII_DEBUG);
        }

        $published = Yii::$app->assetManager->publish(
                $this->getBasePath(), ['forceCopy' => $force, 'except' => ['views/']]
        );

        return $published[1];
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
     * Stores color informations to configuration for use in modules.
     */
    public function storeColorsToConfig()
    {
        $this->loadVariables();

        // @deprecated since version 1.2 color settings
        Yii::$app->settings->set('colorDefault', $this->variable('default'));
        Yii::$app->settings->set('colorPrimary', $this->variable('default'));
        Yii::$app->settings->set('colorInfo', $this->variable('default'));
        Yii::$app->settings->set('colorSuccess', $this->variable('default'));
        Yii::$app->settings->set('colorWarning', $this->variable('default'));
        Yii::$app->settings->set('colorDanger', $this->variable('default'));
    }

    /**
     * Reloads the less variables within the variable.less file and caches the result.
     *
     * Note: this function merges the default less variables with the actual theme variables.
     *
     * @since 1.2
     */
    public function loadVariables()
    {
        $variables = $this->parseThemeVariables('variables.less');

        Yii::$app->cache->delete(self::VARIABLES_CACHE_ID);
        $this->_variables = [];

        // Set variable settings
        foreach ($variables as $variable => $value) {
            $this->_variables[$variable] = $value;
        }

        Yii::$app->cache->set(self::VARIABLES_CACHE_ID, $this->_variables);
    }

    /**
     * Searches for a theme varaible with the given $key.
     *
     * @since 1.2
     * @param string $key
     * @return string
     */
    public function variable($key, $default = null)
    {
        if (empty($this->_variables)) {
            $this->_variables = Yii::$app->cache->get(self::VARIABLES_CACHE_ID);
        }

        if (empty($this->_variables)) {
            $this->loadVariables();
        }

        $result = isset($this->_variables[$key]) ? $this->_variables[$key] : null;

        // Compatibility with old themes prior v1.2
        if(!$result && in_array($key, ['default', 'primary', 'info', 'success', 'warning', 'danger'])) {
            $result = Yii::$app->settings->get('color'.ucfirst($key));
        }

        return $result === null ? $default : $result;
    }

    /**
     * Parses the varaibles of the given less fileName.
     * This will merge the values of the default `@webroot/less/fileName.less` and
     * the actual theme values in `themeBasePath/less/fileName.less`.
     *
     * @since 1.2
     * @param type $lessFile the less file to parse
     */
    public function parseThemeVariables($lessFileName)
    {
        // Parse default values
        $variables = $this->parseLessVariables(Yii::getAlias('@webroot-static/less/'.$lessFileName));

        // Overwrite theme values
        return \yii\helpers\ArrayHelper::merge($variables, $this->parseLessVariables($this->getBasePath() . '/less/'.$lessFileName));
    }

    /**
     * Parses the variables of the given $lessFile.
     *
     * @since 1.2
     * @param string $lessFile
     * @return array key value pair of less variables
     */
    protected function parseLessVariables($lessFile)
    {
        if (file_exists($lessFile)) {
            $variables = [];
            preg_match_all('/@(.*?):\s(.*?);/', file_get_contents($lessFile), $regexResult, PREG_SET_ORDER);
            foreach ($regexResult as $regexHit) {
                $variables[$regexHit[1]] = $regexHit[2];
            }
            return $variables;
        }

        return [];
    }

    /**
     * Store colors to configuration.
     *
     * @deprecated since version 1.1
     * @param type $themeName
     */
    public static function setColorVariables($themeName)
    {
        $theme = ThemeHelper::getThemeByName($themeName);

        if ($theme !== null) {
            $theme->storeColorsToConfig();
        }
    }

}
