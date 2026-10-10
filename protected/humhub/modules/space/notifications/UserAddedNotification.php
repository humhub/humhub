<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationPriority;
use Yii;

/**
 * Notifies a user that they were added to a space directly, without an invitation. The source
 * is the {@see \humhub\modules\space\models\Space}, the originator the user who added them.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `UserAddedNotification`.
 *
 * @since 1.20
 */
final class UserAddedNotification extends BaseNotification
{
    use SpaceNotificationTrait;

    /**
     * @inheritdoc
     */
    public static function category(): NotificationCategory
    {
        return NotificationCategory::direct();
    }

    /**
     * @inheritdoc
     */
    public static function priority(): NotificationPriority
    {
        return NotificationPriority::Normal;
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return Yii::t('SpaceModule.notification', 'You were added to Space {spaceName}', [
            'spaceName' => $params['spaceName'],
        ]);
    }
}
