<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\notifications;

use humhub\modules\friendship\models\Friendship;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
use humhub\modules\notification\components\NotificationPriority;
use Yii;

/**
 * Notifies the requester that a friend request was accepted. The source is the accepting
 * user's {@see Friendship} record.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `RequestApproved` (since 1.1).
 *
 * @since 1.20
 */
final class FriendshipApprovedNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public static function group(): NotificationGroup
    {
        return NotificationGroup::direct();
    }

    /**
     * @inheritdoc
     */
    public static function priority(): NotificationPriority
    {
        return NotificationPriority::Normal;
    }

    /**
     * The profile of the originator.
     *
     * @inheritdoc
     */
    public function getUrl(bool $scheme = false): ?string
    {
        return $this->originator?->getUrl($scheme);
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return Yii::t('FriendshipModule.notification', '{displayName} accepted your friend request.', [
            'displayName' => $params['displayName'],
        ]);
    }
}
