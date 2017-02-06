<?php

namespace humhub\modules\comment\notifications;

use Yii;
use humhub\modules\notification\components\NotificationCategory;

/**
 * Description of CommentNotificationCategory
 *
 * @author buddha
 */
class CommentNotificationCategory extends NotificationCategory
{

    /**
     * @inheritdoc
     */
    public $id = "comments";

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('CommentModule.notifications_NotificationCategory', 'Receive Notifications when someone comments on my own or a following post.');
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('CommentModule.notifications_NotificationCategory', 'Comments');
    }

}
