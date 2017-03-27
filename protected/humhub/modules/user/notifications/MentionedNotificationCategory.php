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

    /**
     * @inheritdoc
     */
    public $id = 'mentioned';

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('UserModule.notifications_FollowingNotificationCategory', 'Mentionings');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('UserModule.notifications_FollowingNotificationCategory', 'Receive Notifications when someone mentioned you in a post.');
    }

}
