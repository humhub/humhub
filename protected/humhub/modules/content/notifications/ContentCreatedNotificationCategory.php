<?php

namespace humhub\modules\content\notifications;

use Yii;
use humhub\modules\notification\components\NotificationCategory;

/**
 * Description of ContentCreatedNotificationCategory
 *
 * @author buddha
 */
class ContentCreatedNotificationCategory extends NotificationCategory
{

    /**
     * @inheritdoc
     */
    public $id = "content_created";

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('ContentModule.notifications_ContentCreatedNotificationCategory', 'Receive Notifications for new content you follow.');
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('ContentModule.notifications_ContentCreatedNotificationCategory', 'New Content');
    }

}
