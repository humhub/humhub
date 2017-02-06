<?php

namespace humhub\modules\notification\components;

/**
 * The WebTargetRenderer is used to render Notifications for the WebNotificationTarget.
 * 
 * @author buddha
 */
class WebTargetRenderer extends \humhub\components\rendering\DefaultViewPathRenderer
{
    /**
     * @inheritdoc
     */
    public $defaultView = '@notification/views/default.php';

    /**
     * @inheritdoc
     */
    public $defaultViewPath = '@notification/views';
}
