<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;

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

    public function init()
    {

        $this->setBasePath('@webroot/themes/' . $this->name);
        $this->pathMap = [
            '@humhub/views' => '@webroot/themes/' . $this->name . '/views',
        ];
        parent::init();
    }

    private $_baseUrl = null;

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
        // .../moduleId/views/controllerId/viewName.php
        if (preg_match('@.*/(.*?)/views/(.*?)/(.*?)\.php$@', $path, $hits)) {
            return $this->getBasePath() . '/views/' . $hits[1] . '/' . $hits[2] . '/' . $hits[3] . '.php';
        }

        // /moduleId/[widgets|activities|notifications]/views/viewName.php
        if (preg_match('@.*/(.*?)/(widgets|notifications|activities)/views/(.*?)\.php$@', $path, $hits)) {
            return $this->getBasePath() . '/views/' . $hits[1] . '/' . $hits[2] . '/' . $hits[3] . '.php';
        }

        return null;
    }

    /**
     * Returns an array of all installed themes.
     *
     * @return Array
     */
    public static function getThemes()
    {
        $themes = array();
        $themePath = \Yii::getAlias('@webroot/themes');

        foreach (scandir($themePath) as $file) {
            if ($file == "." || $file == ".." || !is_dir($themePath . DIRECTORY_SEPARATOR . $file)) {
                continue;
            }
            $themes[$file] = $file;
        }
        return $themes;
    }

}
