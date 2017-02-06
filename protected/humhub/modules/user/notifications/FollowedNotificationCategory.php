<?php

namespace humhub\modules\user\notifications;

use Yii;
use humhub\modules\notification\components\NotificationCategory;

/**
 * Description of FollowingNotificationCategory
 *
 * @author buddha
 */
class FollowedNotificationCategory extends NotificationCategory
{

    public $id = 'followed';

    public function getTitle()
    {
        return Yii::t('UserModule.notifications_FollowingNotificationCategory', 'Following');
    }

    public function getDescription()
    {
        return Yii::t('UserModule.notifications_FollowingNotificationCategory', 'Receive Notifications when someone is following you.');
    }

}
