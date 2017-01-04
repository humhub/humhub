<?php

namespace humhub\components\rendering;

use Yii;

/**
 * A LayoutRenderer subclass can be used to render layout based views by setting the $viewPath and $layout properties.
 * 
 * The $viewPath defines the path where the target view file resides.
 * For a viewable with the viewName 'myView.php' the renderer will render the view:
 * 
 * '<viewPath>/myView.php'
 * 
 * where viewPath can also be provided as a Yii alias.
 * 
 * The rendered view will be embeded into the given $layout which should point to the layout file 
 * and can also be provided as a Yii alias e.g:
 * 
 * '@myModule/views/layouts/myLayout.php'
 *
 * @author buddha
 */
class LayoutRenderer extends ViewPathRenderer
{

    /**
     * @var string layout file path
     */
    public $layout;

    /**
     * @inheritdoc
     */
    public function render(Viewable $viewable, $params = [])
    {
        // Render the view itself
        $viewParams = $viewable->getViewParams($params);
        
        if(!isset($viewParams['content'])) {
            $viewParams['content'] = parent::renderView($viewable, $viewParams);
        }
        
        $layout = $this->getLayout($viewable);
        
        // Embed view into layout if provided
        if ($layout) {
            return Yii::$app->getView()->renderFile($layout, $viewParams, $viewable);
        } else {
            return $viewParams['content'];
        }
    }
    
    protected function getLayout(Viewable $viewable)
    {
        return $this->layout;
    }

}
