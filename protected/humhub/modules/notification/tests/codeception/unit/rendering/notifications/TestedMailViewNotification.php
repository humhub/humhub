<?php

namespace humhub\modules\notification\tests\codeception\unit\rendering\notifications;

/**
 * Description of TestedDefaultViewNotification
 *
 * @author buddha
 */
class TestedMailViewNotification extends \humhub\modules\notification\components\BaseNotification
{
    public function html()
    {
        return '<h1>TestedMailViewNotificationHTML</h1>';
    }
    
    public function text()
    {
        return 'TestedMailViewNotificationText';
    }
}
