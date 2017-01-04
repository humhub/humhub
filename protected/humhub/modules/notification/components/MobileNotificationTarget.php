<?php
namespace humhub\modules\notification\components;

use Yii;
use humhub\modules\user\models\User;

/**
 *
 * @author buddha
 */
class MobileNotificationTarget extends NotificationTarget
{
    /**
     * @inheritdoc
     */
    public $id = 'mobile';
    
    /**
     * Used to forward a BaseNotification object to a NotificationTarget.
     * The NotificationTarget should handle the notification by pushing a Job to
     * a Queue or directly handling the notification.
     * 
     * @param BaseNotification $notification
     */
    public function handle(BaseNotification $notification, User $user) {
        
    }

    public function getTitle()
    {
        
    }
}
