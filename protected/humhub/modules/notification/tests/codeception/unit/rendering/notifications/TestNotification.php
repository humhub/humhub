<?php

namespace humhub\modules\notification\tests\codeception\unit\rendering\notifications;

/**
 * Description of TestedDefaultViewNotification
 *
 * @author buddha
 */
class TestNotification extends \humhub\modules\notification\components\BaseNotification
{
    public $moduleId = 'notification';
    public $requireOriginator = false;
    public $requireSource = false;

    public function html()
    {
        return '<h1>TestedMailViewNotificationHTML</h1>';
    }
    
    public function text()
    {
        return 'TestedMailViewNotificationText';
    }
}
