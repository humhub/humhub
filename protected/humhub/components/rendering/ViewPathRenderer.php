<?php

namespace humhub\components\rendering;

use Yii;

/**
 * A ViewPathRenderer is a simple Renderer implementation for rendering Viewable
 * instances by searching for the Viewable viewName within the given $viewPath.
 * 
 * If no $viewPath is given, we'll determine the view path of the viewable as following:
 * 
 * ViewableClassPath/../views
 * 
 * @author buddha
 */
class ViewPathRenderer extends \yii\base\Object implements Renderer
{

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
     * @return type
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
     * @return type
     */
    public function getViewFile(Viewable $viewable)
    {
        return $this->getViewPath($viewable) . '/' . $viewable->getViewName();
    }

    /**
     * Returns the directory containing the view files for this event.
     * The default implementation returns the 'views' subdirectory under the directory containing the notification class file.
     * @return string the directory containing the view files for this notification.
     */
    public function getViewPath(Viewable $viewable)
    {
        if ($this->viewPath !== null) {
            return Yii::getAlias($this->viewPath);
        }

        $class = new \ReflectionClass($viewable);
        return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views';
    }

}
