<?php
namespace humhub\modules\space\notifications;

use Yii;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationTarget;

/**
 * Description of SpaceJoinNotificationCategory
 *
 * @author buddha
 */
class SpaceMemberNotificationCategory extends NotificationCategory
{
    /**
     * Category Id
     * @var string 
     */
    public $id = 'space_member';

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('SpaceModule.notifications_SpaceMemberNotificationCategory', 'Space Membership');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('SpaceModule.notifications_SpaceMemberNotificationCategory', 'Receive Notifications for Space Approval and Invite events');
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
