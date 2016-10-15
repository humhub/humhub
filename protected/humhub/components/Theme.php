<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
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
        $lessFileName = $this->getBasePath() . '/css/theme.less';
        if (file_exists($lessFileName)) {
            $less = file_get_contents($lessFileName);

            $startDefault = strpos($less, '@default') + 10;
            $startPrimary = strpos($less, '@primary') + 10;
            $startInfo = strpos($less, '@info') + 7;
            $startSuccess = strpos($less, '@success') + 10;
            $startWarning = strpos($less, '@warning') + 10;
            $startDanger = strpos($less, '@danger') + 9;
            $length = 7;

            Yii::$app->settings->set('colorDefault', substr($less, $startDefault, $length));
            Yii::$app->settings->set('colorPrimary', substr($less, $startPrimary, $length));
            Yii::$app->settings->set('colorInfo', substr($less, $startInfo, $length));
            Yii::$app->settings->set('colorSuccess', substr($less, $startSuccess, $length));
            Yii::$app->settings->set('colorWarning', substr($less, $startWarning, $length));
            Yii::$app->settings->set('colorDanger', substr($less, $startDanger, $length));
        }
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
