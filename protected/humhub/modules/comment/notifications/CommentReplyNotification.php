<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\notifications;

use humhub\models\RecordMap;
use humhub\modules\comment\models\Comment;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\Grouping;
use humhub\modules\notification\components\NotificationBlock;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationContext;
use humhub\modules\notification\models\Notification;
use Yii;

/**
 * Notifies the author of a comment about a reply to it. The source is the replied-to (root)
 * {@see Comment}, the reply travels in the payload (`replyId`).
 *
 * The sentence names the replied-to comment, the notification links to the reply (highlighted
 * through the comment permalink) and the mail previews it. The replies to one comment are grouped.
 *
 * Sent by the comment as part of a {@see \humhub\modules\notification\components\NotificationSet}:
 * a mention in the reply ranks above it, the new comment notification of the content's followers
 * below it.
 *
 * @since 1.20
 */
final class CommentReplyNotification extends BaseNotification
{
    private Comment|false|null $_reply = false;

    /**
     * @inheritdoc
     */
    public static function category(): NotificationCategory
    {
        return NotificationCategory::direct();
    }

    /**
     * The replies to one comment.
     *
     * @inheritdoc
     */
    public static function grouping(): ?Grouping
    {
        return Grouping::bySource();
    }

    /**
     * The replied-to comment.
     *
     * @inheritdoc
     */
    public function getSubjectRecord(): ?ContentOwner
    {
        return $this->sourceRecord instanceof ContentOwner ? $this->sourceRecord : parent::getSubjectRecord();
    }

    /**
     * The reply, highlighted; the replied-to comment when the reply is gone.
     *
     * @inheritdoc
     */
    public function getUrl(bool $scheme = false): ?string
    {
        return $this->getReply()?->getUrl($scheme) ?? parent::getUrl($scheme);
    }

    /**
     * The preview of the reply.
     *
     * @inheritdoc
     */
    public function getBlocks(NotificationContext $context): array
    {
        $reply = $this->getReply();

        return $reply !== null
            ? [NotificationBlock::contentPreview($reply)]
            : parent::getBlocks($context);
    }

    /**
     * The reply this notification is about, `null` when it is gone.
     *
     * @api
     */
    public function getReply(): ?Comment
    {
        if ($this->_reply === false) {
            $replyId = $this->payload['replyId'] ?? null;
            $this->_reply = $replyId !== null && $this->sourceRecord instanceof Comment
                ? Comment::findOne(['id' => (int)$replyId, 'parent_comment_id' => $this->sourceRecord->id])
                : null;
        }

        return $this->_reply;
    }

    /**
     * Deletes the notifications about the given reply - when it is deleted, since the notification
     * is stored about the replied-to comment.
     *
     * @internal called by {@see Comment::afterDelete()}
     */
    public static function revokeReply(Comment $reply): void
    {
        $parent = $reply->parentComment;
        if ($parent === null || !RecordMap::hasId($parent)) {
            return;
        }

        $rows = Notification::find()
            ->andWhere([
                'notification.class' => self::class,
                'notification.source_record_id' => RecordMap::getId($parent),
                'notification.originator_id' => $reply->created_by,
            ])
            ->all();

        foreach ($rows as $row) {
            $payload = is_string($row->payload) ? json_decode($row->payload, true) : $row->payload;
            if ((int)(is_array($payload) ? $payload['replyId'] ?? 0 : 0) === (int)$reply->id) {
                $row->delete();
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} replied to your {content}.', $params);
    }
}
