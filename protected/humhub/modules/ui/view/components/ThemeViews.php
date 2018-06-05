<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\view\components;

use humhub\modules\file\libs\FileHelper;
use yii\base\Component;

/**
 * ThemeViews component determines provided view files of a theme.
 *
 * @since 1.3
 * @package humhub\modules\ui\view\components
 */
class ThemeViews extends Component
{
    /**
     * @var Theme
     */
    public $theme;


    /**
     * Converts a file to a themed file if possible.
     * If no view theme is available for the given view path null is returned.
     *
     * @param $path
     * @return string|null the translated file name or null
     */
    public function translate($path)
    {
        $translated = $this->legacyTranslate($path);

        if ($translated !== null && is_file($translated)) {
            return $translated;
        }

        $translated = $this->legacyTranslateResource($path);
        if ($translated !== null) {
            return $translated;
        }

        return null;
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
    protected function legacyTranslate($path)
    {
        $sep = preg_quote(DIRECTORY_SEPARATOR);
        $path = FileHelper::normalizePath($path);

        // .../moduleId/views/controllerId/viewName.php
        if (preg_match('@.*' . $sep . '(.*?)' . $sep . 'views' . $sep . '(.*?)' . $sep . '(.*?)\.php$@', $path, $hits)) {
            return $this->theme->getBasePath() . '/views/' . $hits[1] . '/' . $hits[2] . '/' . $hits[3] . '.php';
        }

        // /moduleId/[widgets|activities|notifications]/views/viewName.php
        if (preg_match('@.*' . $sep . '(.*?)' . $sep . '(widgets|notifications|activities)' . $sep . 'views' . $sep . '(.*?)\.php$@', $path, $hits)) {
            // Handle special case (protected/humhub/widgets/views/view.php => views/widgets/view.php
            if ($hits[1] == 'humhub') {
                return $this->theme->getBasePath() . '/views/' . $hits[2] . '/' . $hits[3] . '.php';
            }
            return $this->theme->getBasePath() . '/views/' . $hits[1] . '/' . $hits[2] . '/' . $hits[3] . '.php';
        }

        return null;
    }

    protected function legacyTranslateResource($path)
    {
        // Web Resource e.g. image
        if (substr($path, 0, 5) === '@web/' || substr($path, 0, 12) === '@web-static/') {

            $themedFile = str_replace(['@web/', '@web-static/'], [$this->theme->getBasePath(), $this->theme->getBasePath() . DIRECTORY_SEPARATOR . '/'], $path);

            // Check if file exists in theme base dir
            if (file_exists($themedFile)) {
                return str_replace(['@web/', '@web-static/'], [$this->theme->getBaseUrl(), $this->theme->getBaseUrl() . DIRECTORY_SEPARATOR . '/'], $path);
            }
            return $path;
        }

        return null;
    }

}
