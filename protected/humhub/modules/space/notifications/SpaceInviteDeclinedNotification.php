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
 * Notifies the inviter that the invited user declined the invitation. The source is the
 * {@see \humhub\modules\space\models\Space}, the originator the invited user.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `InviteDeclined` (since 0.5).
 *
 * @since 1.20
 */
final class SpaceInviteDeclinedNotification extends BaseNotification
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
        return Yii::t('SpaceModule.notification', '{displayName} declined your invite for the space {spaceName}', [
            'displayName' => $params['displayName'],
            'spaceName' => $params['spaceName'],
        ]);
    }
}
