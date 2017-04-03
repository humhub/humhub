<?php

namespace humhub\modules\notification\tests\codeception\unit\category\notifications;

use humhub\modules\user\models\User;
use humhub\modules\notification\targets\BaseTarget;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\notification\targets\MailTarget;

/**
 * Description of TestedDefaultViewNotification
 *
 * @author buddha
 */
class SpecialNotificationCategory extends \humhub\modules\notification\components\NotificationCategory
{

    public $id = 'test_special';

    public function getDefaultSetting(BaseTarget $target)
    {
        if ($target->id === MailTarget::getId()) {
            return false;
        } else if ($target->id === WebTarget::getId()) {
            return false;
        }

        return $target->defaultSetting;
    }

    public function getFixedSettings()
    {
        return [MailTarget::getId()];
    }

    public function isVisible(User $user = null)
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
