<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\notifications;

use humhub\components\grouping\Grouping;
use humhub\modules\comment\models\Comment;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationBlock;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationContext;
use humhub\modules\notification\components\NotificationPriority;
use Yii;

/**
 * Notifies the followers of a content - its author included - about a new comment. The source is
 * the {@see Comment}.
 *
 * The sentence is about the commented content ("commented your …" for its author), the mail
 * previews the comment and the notification links to it. The comments on one content are grouped.
 *
 * Sent by the comment as the last entry of a {@see \humhub\modules\notification\components\NotificationSet}:
 * a user mentioned in the comment or the author of the replied-to comment gets that notification instead.
 *
 * Since 1.20 built on the record-bound {@see BaseNotification}, formerly `NewComment` (since 0.5).
 *
 * @since 1.20
 */
final class NewCommentNotification extends BaseNotification
{
    /**
     * @inheritdoc
     */
    public static function category(): NotificationCategory
    {
        return NotificationCategory::social();
    }

    /**
     * @inheritdoc
     */
    public static function priority(): NotificationPriority
    {
        return NotificationPriority::Normal;
    }

    /**
     * The comments on one content.
     *
     * @inheritdoc
     */
    public static function grouping(): ?Grouping
    {
        return Grouping::byContent();
    }

    /**
     * The preview of the comment itself.
     *
     * @inheritdoc
     */
    public function getBlocks(NotificationContext $context): array
    {
        return $this->sourceRecord instanceof Comment
            ? [NotificationBlock::contentPreview($this->sourceRecord)]
            : parent::getBlocks($context);
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        return $this->isContentAuthor()
            ? Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} commented your {content}.', $params)
            : Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} commented {content}.', $params);
    }

    /**
     * @inheritdoc
     */
    protected function getRedactedMessage(array $params): ?string
    {
        return $this->isContentAuthor()
            ? Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} commented on your {contentType}.', $params)
            : Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} commented on a {contentType}.', $params);
    }

    /**
     * @inheritdoc
     */
    protected function getMailSubject(array $params): string
    {
        if (!isset($params['content'])) {
            return parent::getMailSubject($params);
        }

        $space = $this->getSpace();
        $params['space'] = $space?->displayName;

        if ($this->isContentAuthor()) {
            return $space
                ? Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} just commented your {content} in Space {space}', $params)
                : Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} just commented your {content}', $params);
        }

        return $space
            ? Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} commented {content} in Space {space}', $params)
            : Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} commented {content}', $params);
    }

    /**
     * Whether the recipient is the author of the commented content.
     */
    private function isContentAuthor(): bool
    {
        return $this->content !== null && (int)$this->content->created_by === (int)$this->recipient->id;
    }
}
