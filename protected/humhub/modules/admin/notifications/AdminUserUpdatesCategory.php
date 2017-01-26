<?php
namespace humhub\modules\admin\notifications;

use Yii;
use humhub\modules\notification\components\NotificationCategory;

/**
 * Description of AdminUserUpdatesCategory
 *
 * @author buddha
 */
class AdminUserUpdatesCategory extends NotificationCategory
{
    public $id = 'admin_user_updates';
    public $sortOrder = 99999 * 99999 * 99999;
    
    public function getDescription()
    {
        return Yii::t('AdminModule.notifications_AdminUserUpdatesCategory', 'Receive Notifications for user events to all admins like new comments ect.');
    }

    public function getTitle()
    {
        return Yii::t('AdminModule.notifications_AdminUserUpdatesCategory', 'Admin User Updates');
    }
}