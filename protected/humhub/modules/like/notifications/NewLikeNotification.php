<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\notifications;

use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\Grouping;
use humhub\modules\notification\components\NotificationCategory;
use Yii;

/**
 * Notifies the author of a content or a comment that someone likes it. The source is the liked
 * record. The likes of one record are grouped.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `NewLike`.
 *
 * @since 1.20
 */
final class NewLikeNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public static function category(): NotificationCategory
    {
        return NotificationCategory::social();
    }

    /**
     * The likes of one record: of a content, or of one comment on it.
     *
     * @inheritdoc
     */
    public static function grouping(): ?Grouping
    {
        return Grouping::byContent()->andSource();
    }

    /**
     * The liked record: a comment, else the content's record.
     *
     * @inheritdoc
     */
    public function getSubjectRecord(): ?ContentOwner
    {
        return $this->sourceRecord instanceof ContentOwner ? $this->sourceRecord : parent::getSubjectRecord();
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return Yii::t('LikeModule.notifications', '{namedCount, plural, =2{{displayNames} like} other{{displayName} likes}} {content}.', $params);
    }

    /**
     * @inheritdoc
     */
    protected function getMailSubject(array $params): string
    {
        return Yii::t('LikeModule.notifications', '{namedCount, plural, =2{{displayNames} like} other{{displayName} likes}} your {content}.', $params);
    }
}
