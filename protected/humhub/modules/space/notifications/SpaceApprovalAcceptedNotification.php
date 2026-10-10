<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;
use Yii;

/**
 * Notifies an applicant that their membership request was approved. The source is the
 * {@see \humhub\modules\space\models\Space}, the originator the approving user.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `ApprovalRequestAccepted` (since 0.5).
 *
 * @since 1.20
 */
final class SpaceApprovalAcceptedNotification extends BaseNotification
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
    protected function getMessage(array $params): string
    {
        return Yii::t('SpaceModule.notification', '{displayName} approved your membership for the space {spaceName}', [
            'displayName' => $params['displayName'],
            'spaceName' => $params['spaceName'],
        ]);
    }
}
