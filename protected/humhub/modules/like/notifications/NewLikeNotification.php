<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\notifications;

use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\notification\components\ActiveQueryNotification;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
use humhub\modules\notification\models\Notification;
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
    public static function group(): NotificationGroup
    {
        return NotificationGroup::social();
    }

    /**
     * @inheritdoc
     */
    public function getGroupingQuery(): ?ActiveQueryNotification
    {
        if ($this->content === null) {
            return null;
        }

        return Notification::find()
            ->andWhere(['notification.class' => self::class])
            ->andWhere(['notification.content_id' => $this->content->id])
            ->andWhere(['notification.source_record_id' => $this->record->source_record_id]);
    }

    /**
     * The liked record: a comment, else the content's record.
     *
     * @inheritdoc
     */
    protected function getContentOwner(): ?ContentOwner
    {
        return $this->sourceRecord instanceof ContentOwner ? $this->sourceRecord : parent::getContentOwner();
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        if ($this->groupCount > 1) {
            return Yii::t('LikeModule.notifications', '{displayNames} likes {contentTitle}.', [
                'displayNames' => $params['displayNames'],
                'contentTitle' => $params['content'],
            ]);
        }

        return Yii::t('LikeModule.notifications', '{displayName} likes {contentTitle}.', [
            'displayName' => $params['displayName'],
            'contentTitle' => $params['content'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getMailSubject(): string
    {
        $params = $this->getMessageParamsPlain($this->webContentLength);

        if ($this->groupCount > 1) {
            return Yii::t('LikeModule.notifications', '{displayNames} likes your {contentTitle}.', [
                'displayNames' => $params['displayNames'],
                'contentTitle' => $params['content'],
            ]);
        }

        return Yii::t('LikeModule.notifications', '{displayName} likes your {contentTitle}.', [
            'displayName' => $params['displayName'],
            'contentTitle' => $params['content'],
        ]);
    }
}
