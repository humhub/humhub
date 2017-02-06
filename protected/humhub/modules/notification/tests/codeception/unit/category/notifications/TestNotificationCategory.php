<?php

namespace humhub\modules\notification\tests\codeception\unit\category\notifications;


use humhub\modules\notification\components\NotificationTarget;

/**
 * Description of TestedDefaultViewNotification
 *
 * @author buddha
 */
class TestNotificationCategory extends \humhub\modules\notification\components\NotificationCategory
{

    public $id = 'test';

    public function getDefaultSetting(NotificationTarget $target)
    {
        if ($target->id === \humhub\modules\notification\components\MailNotificationTarget::getId()) {
            return false;
        } else if ($target->id === \humhub\modules\notification\components\WebNotificationTarget::getId()) {
            return true;
        }

        return $target->defaultSetting;
    }
    

    public function getDescription()
    {
        return 'My Test Notification Category';
    }

    public function getTitle()
    {
        return 'Test Category';
    }

}
