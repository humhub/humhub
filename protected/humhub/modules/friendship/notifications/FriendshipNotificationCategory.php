<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\notifications;

use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\user\models\User;
use Yii;

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
        return Yii::t('FriendshipModule.notification', 'Friendship');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('FriendshipModule.notification', 'Receive Notifications for Friendship Request and Approval events.');
    }

    /**
     * @inheritdoc
     */
    public function isVisible(User $user = null)
    {
        return Yii::$app->getModule('friendship')->isFriendshipEnabled();
    }

}
