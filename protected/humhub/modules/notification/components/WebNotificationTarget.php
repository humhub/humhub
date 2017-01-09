<?php
namespace humhub\modules\notification\components;

use Yii;
use humhub\modules\user\models\User;

/**
 *
 * @author buddha
 */
class WebNotificationTarget extends NotificationTarget
{
    /**
     * @inheritdoc
     */
    public $id = 'web';
    
    /**
     * Since the WebNotificationTarget only requires the Notification ActiveRecord to be persisted,
     * this handler only check the presence of the related Notification record.
     */
    public function handle(BaseNotification $notification, User $user) {
        if(!$notification->record) {
            throw new \yii\base\Exception('Notification record not found for BaseNotification "'.$notification->className().'"');
        }
    }
    
    /**
     * @inheritdoc
     */
    public function isEditable(User $user = null)
    {
        return false;
    }

    public function getTitle()
    {
        return Yii::t('NotificationModule.components_WebNotificationTarget', 'Web');
    }
}
