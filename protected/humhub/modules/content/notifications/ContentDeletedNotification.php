<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationPriority;
use Yii;

/**
 * Notifies the author when an administrator deletes their content (e.g. a post). Without a
 * source - the content is gone; the payload carries the plain text `contentTitle` and the
 * `reason` given by the administrator.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `ContentDeleted`. Since 1.20 it
 * reaches every enabled channel (mail, push), before it was a web notification only.
 *
 * @since 1.20
 */
final class ContentDeletedNotification extends BaseNotification
{
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
        return Yii::t('ContentModule.notifications', 'Your {contentTitle} has been deleted by {displayName} for \'{reason}\'', $params);
    }

    /**
     * @inheritdoc
     */
    protected function getMessageParams(): array
    {
        return [
            'contentTitle' => (string)($this->payload['contentTitle'] ?? ''),
            'reason' => (string)($this->payload['reason'] ?? ''),
        ];
    }
}
