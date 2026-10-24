<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\notifications;

use humhub\modules\friendship\models\Friendship;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;
use Yii;

/**
 * Notifies a user about a friend request. The source is the requester's {@see Friendship} record.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `Request` (since 1.1).
 *
 * @since 1.20
 */
final class FriendshipRequestNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public static function category(): NotificationCategory
    {
        return NotificationCategory::direct();
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
        return Yii::t('FriendshipModule.notification', '{displayName} sent you a friend request.', [
            'displayName' => $params['displayName'],
        ]);
    }
}
