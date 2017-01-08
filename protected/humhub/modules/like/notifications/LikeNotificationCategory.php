<?php

namespace humhub\modules\like\notifications;

use Yii;
use humhub\modules\notification\components\NotificationCategory;

/**
 * Description of LikeNotificationCategory
 *
 * @author buddha
 */
class LikeNotificationCategory extends NotificationCategory
{

    public $id = 'like';

    public function getTitle()
    {
        return Yii::t('LikeModule.notifications_LikeNotificationCategory', 'Likes');
    }

    public function getDescription()
    {
        return Yii::t('LikeModule.notifications_LikeNotificationCategory', 'Receive Notifications when someone likes your content.');
    }

}
