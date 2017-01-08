<?php

namespace humhub\modules\user\notifications;

use Yii;
use humhub\modules\notification\components\NotificationCategory;

/**
 * Description of MentionedNotificationCategory
 *
 * @author buddha
 */
class MentionedNotificationCategory extends NotificationCategory
{

    public $id = 'mentioned';

    public function getTitle()
    {
        return Yii::t('UserModule.notifications_FollowingNotificationCategory', 'Mentionings');
    }

    public function getDescription()
    {
        return Yii::t('UserModule.notifications_FollowingNotificationCategory', 'Receive Notifications when someone mentioned you in a post.');
    }

}
