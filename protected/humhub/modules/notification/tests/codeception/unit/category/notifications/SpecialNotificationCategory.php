<?php

namespace humhub\modules\notification\tests\codeception\unit\category\notifications;


use humhub\modules\notification\components\NotificationTarget;

/**
 * Description of TestedDefaultViewNotification
 *
 * @author buddha
 */
class SpecialNotificationCategory extends \humhub\modules\notification\components\NotificationCategory
{

    public $id = 'test_special';

    public function getDefaultSetting(NotificationTarget $target)
    {
        if ($target->id === \humhub\modules\notification\components\MailNotificationTarget::getId()) {
            return false;
        } else if ($target->id === \humhub\modules\notification\components\WebNotificationTarget::getId()) {
            return false;
        }

        return $target->defaultSetting;
    }
    
    public function getFixedSettings()
    {
        return [\humhub\modules\notification\components\MailNotificationTarget::getId()];
    }
    
    public function isVisible(\humhub\modules\user\models\User $user = null)
    {
        return !$user || $user->id != 2;
    }

    public function getDescription()
    {
        return 'My Special Test Notification Category';
    }

    public function getTitle()
    {
        return 'Test Special Category';
    }

}
