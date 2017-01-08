<?php

namespace humhub\modules\activity\components;

use Yii;
use humhub\components\rendering\Viewable;

/**
 * The WebTargetRenderer is used to render Notifications for the WebNotificationTarget.
 *
 * A BaseNotification can overwrite the default view and layout by setting a specific $viewName and
 * defining the following files:
 * 
 * Overwrite default view for this notification:
 * @module/views/notification/viewname.php
 * 
 * Overwrite default layout for this notification:
 * @module/views/layouts/notification/viewname.php
 * 
 * @author buddha
 */
class WebRenderer extends \humhub\components\rendering\LayoutRenderer
{

    /**
     * @var string default view path 
     */
    public $defaultViewPath = '@activity/views';

    /**
     * @var string default layout
     */
    public $defaultLayout = '@humhub/modules/activity/views/layouts/web.php';
    
    /**
     * @inheritdoc
     */
    public function render(Viewable $viewable, $params = [])
    {
        if(!$this->getViewFile($viewable)) {
            $params['content'] = $viewable->html();
        }
        
        return parent::render($viewable, $params);
    }

    /**
     * Returns the view file for the given Viewable Notification.
     * 
     * This function will search for the view file defined in the Viewable within the module/views/mail directory of
     * the viewable module.
     * 
     * If the module view does not exist we search for the viewName within the default notification viewPath.
     * 
     * If this view also does not exist we return the base notification view file.
     *
     * @param \humhub\modules\notification\components\Viewable $viewable
     * @return string view file of this notification
     */
    public function getViewFile(Viewable $viewable)
    {
        $viewFile = $this->getViewPath($viewable) . DIRECTORY_SEPARATOR . $viewable->getViewName();
        
        if (!file_exists($viewFile)) {
            $viewFile = Yii::getAlias($this->defaultViewPath) . DIRECTORY_SEPARATOR . $viewable->getViewName();
        }

        if (!file_exists($viewFile)) {
            return null;
        }

        return $viewFile;
    }

    /**
     * Returns the layout for the given Notification Viewable.
     * 
     * This function will search for a layout file under module/views/layouts/mail with the view name defined
     * by $viwable.
     * 
     * If this file does not exists the default notification mail layout will be returned.
     * 
     * @param \humhub\modules\notification\components\Viewable $viewable
     * @return type
     */
    public function getLayout(Viewable $viewable)
    {
        $layout = $this->getViewPath($viewable) . '/layouts/' . $viewable->getViewName();

        if (!file_exists($layout)) {
            $layout = Yii::getAlias($this->defaultLayout);
        }

        return $layout;
    }

}
