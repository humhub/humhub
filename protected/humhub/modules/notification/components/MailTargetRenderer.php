<?php

namespace humhub\modules\notification\components;

use Yii;
use humhub\components\rendering\Viewable;

/**
 * The MailTargetRenderer is used to render Notifications for the MailNotificationTarget.
 * 
 * A BaseNotification can overwrite the default view and layout by setting a specific $viewName and
 * defining the following files:
 * 
 * Overwrite default html view for this notification:
 * @module/views/notification/mail/viewname.php
 * 
 * Overwrite default mail layout for this notification:
 * @module/views/layouts/notification/mail/viewname.php
 * 
 * Overwrite default mail text layout for this notification:
 * @module/views/layouts/notification/mail/plaintext/viewname.php
 *
 * @author buddha
 */
class MailTargetRenderer extends \humhub\components\rendering\MailLayoutRenderer
{

    /**
     * @var string default notification mail view path 
     */
    public $defaultViewPath = '@notification/views/notification/mail';

    /**
     * @var string default notification mail view 
     */
    public $defaultView = '@notification/views/notification/mail/default.php';

    /**
     * @var string layout file path
     */
    public $defaultLayout = '@notification/views/layouts/mail.php';

    /**
     * @var string default notification mail text view path 
     */
    public $defaultTextViewPath = '@notification/views/notification/mail/plaintext';

    /**
     * @var string text layout file 
     */
    public $defaultTextLayout = "@notification/views/layouts/mail_plaintext.php";

    /**
     * Returns the view file for the given Viewable Notification.
     * 
     * This function will search for the view file defined in the Viewable within the module/views/notification/mail directory of
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
        $viewFile = $this->getViewPath($viewable) . '/notification/mail/' . $viewable->getViewName();

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
        $layout = $this->getViewPath($viewable) . '/layouts/notification/mail/' . $viewable->getViewName();

        if (!file_exists($layout)) {
            $layout = Yii::getAlias($this->defaultLayout);
        }

        return $layout;
    }

    /**
     * Returns the text layout for the given Notification Viewable.
     * 
     * This function will search for a view file under module/views/layouts/mail/plaintext with the view name defined
     * by $viwable.
     * 
     * If this file does not exists the default notification text mail layout is returned.
     * 
     * @param \humhub\modules\notification\components\Viewable $viewable
     * @return type
     */
    public function getTextLayout(Viewable $viewable)
    {
        $layout = $this->getViewPath($viewable) . '/layouts/notification/mail/plaintext/' . $viewable->getViewName();

        if (!file_exists($layout)) {
            $layout = Yii::getAlias($this->defaultTextLayout);
        }

        return $layout;
    }

}
