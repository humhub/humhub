<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\Grouping;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\user\models\Follow;
use Yii;

/**
 * Notifies a user that someone follows them. The source is the {@see Follow} record. The new
 * followers of a user are grouped.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `Followed`.
 *
 * @since 1.20
 */
final class FollowedNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public static function category(): NotificationCategory
    {
        return NotificationCategory::followers();
    }

    /**
     * All new followers of the recipient.
     *
     * @inheritdoc
     */
    public static function grouping(): ?Grouping
    {
        return Grouping::byClass();
    }

    /**
     * The profile of the follower.
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
        return Yii::t('UserModule.notification', '{namedCount, plural, =2{{displayNames} are} other{{displayName} is}} now following you.', $params);
    }
}
