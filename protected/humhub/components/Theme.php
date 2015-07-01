<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

/**
 * @inheritdoc
 */
class Theme extends \yii\base\Theme
{

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

}
