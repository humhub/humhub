<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\notifications;

use Yii;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\targets\BaseTarget;
use humhub\modules\notification\targets\MailTarget;

/**
 * LikeNotificationCategory
 *
 * @author buddha
 */
class LikeNotificationCategory extends NotificationCategory
{

    /**
     * @inheritdoc
     */
    public $id = 'like';

    /**
     * @inheritdoc
     */
    public function getDefaultSetting(BaseTarget $target)
    {
        if ($target instanceof MailTarget) {
            return false;
        }

        return parent::getDefaultSetting($target);
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('LikeModule.notifications_LikeNotificationCategory', 'Likes');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('LikeModule.notifications_LikeNotificationCategory', 'Receive Notifications when someone likes your content.');
    }

}
