<?php

namespace humhub\modules\notification\components;

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
class WebTargetRenderer extends \humhub\components\rendering\LayoutRenderer
{

    /**
     * @var string default view path 
     */
    public $defaultViewPath = '@notification/views/notification';
    
    /*
     * @var string default view
     */
    public $defaultView = '@notification/views/notification/default.php';

    /**
     * @var string default layout
     */
    public $defaultLayout = '@notification/views/layouts/web.php';
    
    /**
     * @var string defines the view subfolder containing layouts and views. 
     */
    public $viewSubFolder = 'notification';

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
        $viewFile = $this->getViewPath($viewable) . '/'.$this->viewSubFolder.'/' . $viewable->getViewName();
        
        if (!file_exists($viewFile)) {
            $viewFile = Yii::getAlias($this->defaultViewPath) . DIRECTORY_SEPARATOR . $viewable->getViewName();
        }

        if (!file_exists($viewFile)) {
            $viewFile = Yii::getAlias($this->defaultView);
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
        $layout = $this->getViewPath($viewable) . '/layouts/'.$this->viewSubFolder.'/' . $viewable->getViewName();

        if (!file_exists($layout)) {
            $layout = Yii::getAlias($this->defaultLayout);
        }

        return $layout;
    }

}
