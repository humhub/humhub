<?php

namespace humhub\modules\notification\tests\codeception\unit\category\notifications;

use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\notification\targets\BaseTarget;

/**
 * Description of TestedDefaultViewNotification
 *
 * @author buddha
 */
class TestNotificationCategory extends \humhub\modules\notification\components\NotificationCategory
{

    public $id = 'test';

    public function getDefaultSetting(BaseTarget $target)
    {
        if ($target->id === MailTarget::getId()) {
            return false;
        } else if ($target->id === webTarget::getId()) {
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
