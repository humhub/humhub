<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\rendering;

use Yii;

/**
 * A ViewPathRenderer is a simple Renderer implementation for rendering Viewables by searching for a matching viewFile relative
 * to the Viewables class path or relative to a given $viewPath.
 *
 * If a $viewPath is given the renderer will search for the view within this path directly.
 *
 * If no $viewPath is given, the ViewPathRenderer will determine the view path relative to the Viewable as following:
 *
 *  - In case $parent = false the renderer will search directly in the class path subdirectory views:
 *
 * `viewableClassPath/views`
 *
 * - in case $parent = true the renderer will search in the parents views folder (e.g. in the modules main view folder):
 *
 * `viewableClassPath/../views`
 *
 * - in case $subPath is given the subPath will be appended to the view path e.g:
 *
 * For a subPath 'mail' and $parent = false the search path will be: `viewableClassPath/views/mail`
 *
 * @author buddha
 * @since 1.2
 */
class ViewPathRenderer extends \yii\base\Object implements Renderer
{

    /**
     * Can be used to search the parent's view folder (e.g. the modules base view folder) for the view file.
     * Otherwise this renderer searches for a direct views subdirectory.
     *
     * This field is ignored if $viewPath is given.
     * @var boolean if set to true the renderer will search in the parents view directory for the view.
     */
    public $parent = false;

    /**
     * @var string a subpath within the view folder used for searching the view e.g mails. This will only be used if $viewPath is not given.
     */
    public $subPath;

    /**
     * @var string view path
     */
    public $viewPath;

    /**
     * Renders the viewable by searching the viewable's viewName within the given viewPath.
     *
     * If no viewPath is given this function uses '../views/viewName' as view file path.
     *
     * @param \humhub\components\rendering\Viewable $viewable
     * @return string
     * @throws ViewNotFoundException if the view file does not exist
     */
    public function render(Viewable $viewable, $params = [])
    {
        return $this->renderView($viewable, $viewable->getViewParams($params));
    }

    /**
     * Helper function for rendering a Viewable with the given viewParams.
     *
     * @param \humhub\components\rendering\Viewable $viewable
     * @param type $viewParams
     * @return type
     */
    public function renderView(Viewable $viewable, $viewParams)
    {
        $viewFile = $this->getViewFile($viewable);

        return Yii::$app->getView()->renderFile($viewFile, $viewParams, $viewable);
    }

    /**
     * Returnes the viewFile of the given Viewable.
     *
     * @param \humhub\components\rendering\Viewable $viewable
     * @return string|null the view filename or null when not exists
     */
    public function getViewFile(Viewable $viewable)
    {
        $viewFileName = $this->suffix($viewable->getViewName());
        $viewPath = $this->getViewPath($viewable);

        if (file_exists($viewPath . DIRECTORY_SEPARATOR . $viewFileName)) {
            return $viewPath . DIRECTORY_SEPARATOR . $viewFileName;
        } elseif (!empty($this->subPath)) {
            // Fallback to original file without subPath
            return $this->getViewPath($viewable, false) . DIRECTORY_SEPARATOR . $viewFileName;
        }

        return null;
    }

    /**
     * Checks if the given $viewName has a file suffix or not.
     * If the viewName does not have a suffix we assume a php file and append '.php'.
     *
     * @param string $viewName
     * @return string vieName with suffix.
     */
    protected function suffix($viewName)
    {
        // If no suffix is given, we assume a php file.
        if (!strpos($viewName, '.')) {
            return $viewName . '.php';
        } else {
            return $viewName;
        }
    }

    /**
     * Returns the directory containing the view files for this event.
     * The default implementation returns the 'views' subdirectory under the directory containing the notification class file.
     *
     * @param Viewable $viewable The viewable
     * @param boolean $useSubPath use the subpath if provided
     * @return string the directory containing the view files for this notification.
     */
    public function getViewPath(Viewable $viewable, $useSubPath = true)
    {
        if ($this->viewPath) {
            return Yii::getAlias($this->viewPath);
        }

        $class = new \ReflectionClass($viewable);

        $dir = ($this->parent) ? dirname(dirname($class->getFileName())) . '/' . 'views' : dirname($class->getFileName()) . '/' . 'views';

        if (!empty($this->subPath) && $useSubPath) {
            $dir .= DIRECTORY_SEPARATOR . $this->subPath;
        }

        return $dir;
    }

}
