<?php
namespace humhub\modules\friendship\notifications;

use Yii;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationTarget;

/**
 * Description of SpaceJoinNotificationCategory
 *
 * @author buddha
 */
class FriendshipNotificationCategory extends NotificationCategory
{
    /**
     * Category Id
     * @var string 
     */
    public $id = 'friendship';

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('SpaceModule.notifications_FriendshipNotificationCategory', 'Friendship');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('SpaceModule.notifications_FriendshipNotificationCategory', 'Receive Notifications for Friendship Request and Approval events.');
    }
    
    /**
     * @inheritdoc
     */
    public function getDefaultSetting(NotificationTarget $target)
    {
        if ($target->id === \humhub\modules\notification\components\MailNotificationTarget::getId()) {
            return true;
        } else if ($target->id === \humhub\modules\notification\components\WebNotificationTarget::getId()) {
            return true;
        } else if ($target->id === \humhub\modules\notification\components\MobileNotificationTarget::getId()) {
            return true;
        }

        return $target->defaultSetting;
    }
}
