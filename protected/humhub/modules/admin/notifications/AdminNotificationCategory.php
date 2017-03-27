<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

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

    /**
     * @inheritdoc
     */
    public $id = 'admin';

    /**
     * @inheritdoc
     */
    public $sortOrder = 100;

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('AdminModule.notifications_AdminNotificationCategory', 'Receive Notifications for administrative events like available updates.');
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('AdminModule.notifications_AdminNotificationCategory', 'Administrative');
    }

}
