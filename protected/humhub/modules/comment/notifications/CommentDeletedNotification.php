<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\notifications;

use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationPriority;
use Yii;

/**
 * Notifies the author when an administrator deletes their comment. Without a source - the
 * comment is gone; the payload carries the plain text `commentText` and the `reason` given by
 * the administrator.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `CommentDeleted`. Since 1.20 it
 * reaches every enabled channel (mail, push), before it was a web notification only.
 *
 * @since 1.20
 */
final class CommentDeletedNotification extends BaseNotification
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
     * Nothing to open: the comment is deleted.
     *
     * @inheritdoc
     */
    public function getActions(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return Yii::t('CommentModule.notifications', 'Your comment \'{commentText}\' has been deleted by {displayName} for \'{reason}\'', $params);
    }

    /**
     * @inheritdoc
     */
    protected function getMessageParams(): array
    {
        return [
            'commentText' => (string)($this->payload['commentText'] ?? ''),
            'reason' => (string)($this->payload['reason'] ?? ''),
        ];
    }
}
