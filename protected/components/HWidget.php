<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * HWidget is the base class for all widgets.
 *
 * @author Lucas Bartholemy <lucas@bartholemy.com>
 * @package humhub.components
 * @since 0.5
 */
class HWidget extends CWidget
{

    /**
     * @var array view paths for different types of widgets
     */
    private static $_viewPaths;

    /**
     * Returns the view path for this widget.
     *
     * Transform:
     *  /protected/modules/moduleId/widgets/views/ to /themes/mytheme/views/moduleId/widgets
     *  /protected/widgets/views/ to /themes/mytheme/views/widgets
     *
     *  Instead of normal yii behavior:
     *  /themes/myTheme/WidgetClass/..
     *
     * @param type $checkTheme
     * @return String
     */
    public function getViewPath($checkTheme = false)
    {

        // Fastlane
        $className = get_class($this);


        if ($checkTheme && ($theme = Yii::app()->getTheme()) !== null) {
            if (isset(self::$_viewPaths[$className]))
                return self::$_viewPaths[$className];

            //   /themes/myTheme/views/
            $path = $theme->getViewPath() . DIRECTORY_SEPARATOR;

            // + moduleId/ - if exists
            $moduleId = $this->getModuleId();
            if ($moduleId)
                $path .= $moduleId . DIRECTORY_SEPARATOR;

            $path .= 'widgets' . DIRECTORY_SEPARATOR;

            if (is_dir($path))
                return self::$_viewPaths[$className] = $path;
        } else {
            $class = new ReflectionClass($className);
            return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views';
        }
    }

    /**
     * Extends CWidgets getViewFile by possibilty to get also themed version
     * of a dotted view Filename
     *
     * @param string $viewName name of the view (without file extension)
     * @return string the view file path. False if the view file does not exist
     * @see CApplication::findLocalizedFile
     */
    public function getViewFile($viewName)
    {
        // a path alias e.g. application.modules.x.y.z.
        if (strpos($viewName, '.') && ($theme = Yii::app()->getTheme()) !== null) {
            $themedFile = $theme->getViewFileAliased($viewName);
            if ($themedFile) {
                return $themedFile;
            }
        }
        return parent::getViewFile($viewName);
    }

    /**
     * Returns the parent moduleId if can determined.
     * This requires that the module is located under /modules/ or /modules_core/.
     *
     * It doesn´t support nested modules.
     * Always the parent moduleId is returned.
     *
     * @return String The Id of Module
     */
    public function getModuleId()
    {

        // Get Directory of current widget class
        $reflector = new ReflectionClass(get_class($this));
        $fileName = $reflector->getFileName();

        // Search for .../modules_core/FINDME/... or .../modules/FINDME/...
        preg_match('/\/modules(?:_core)?\/(.*?)\//', $fileName, $match);

        if (isset($match[1]))
            return $match[1];

        return null;
    }

    /**
     * Adds the createUrl functionality to widget by passing it to the current
     * controller.
     *
     * @param String $action
     * @param Array $params
     * @param String $ampersand
     * @return String url
     */
    public function createUrl($action, $params = array(), $ampersand = '&')
    {
        return $this->getController()->createUrl($this->actionPrefix . $action, $params, $ampersand);
    }

}

?>
