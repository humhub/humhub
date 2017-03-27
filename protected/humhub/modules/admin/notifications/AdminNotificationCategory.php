<?php

namespace humhub\modules\admin\notifications;

use Yii;
use humhub\modules\notification\components\NotificationCategory;

/**
 * Description of AdminNotificationCategory
 *
 * @author buddha
 */
class AdminNotificationCategory extends NotificationCategory
{

    public $id = 'admin';

    public $sortOrder = 100;

    public function getDescription()
    {
        return Yii::t('AdminModule.notifications_AdminNotificationCategory', 'Receive Notifications for administrative events like available updates.');
    }

    public function getTitle()
    {
        return Yii::t('AdminModule.notifications_AdminNotificationCategory', 'Administrative');
    }

}
