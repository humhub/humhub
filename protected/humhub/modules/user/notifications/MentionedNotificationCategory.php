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
        return Yii::t('UserModule.notification', 'Mentionings');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('UserModule.notification', 'Receive Notifications when someone mentioned you in a post.');
    }

}
