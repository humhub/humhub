<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\notifications;

use humhub\modules\comment\models\Comment;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\Grouping;
use humhub\modules\notification\components\NotificationBlock;
use humhub\modules\notification\components\NotificationCategory;
use humhub\modules\notification\components\NotificationContext;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\models\Notification;
use humhub\modules\user\models\User;
use humhub\modules\user\notifications\MentionedNotification;
use Yii;

/**
 * Notifies the followers of a content about a new comment. The source is the {@see Comment}.
 *
 * The sentence is about the commented content, the mail previews the comment and the
 * notification links to it. The comments on one content are grouped.
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
     * Not for a user who is mentioned in the comment - the mention notification covers it.
     *
     * @inheritdoc
     */
    public function canReceive(User $user): bool
    {
        if ($this->record->source_record_id === null) {
            return true;
        }

        return !Notification::find()
            ->andWhere([
                'notification.class' => MentionedNotification::class,
                'notification.user_id' => $user->id,
                'notification.source_record_id' => $this->record->source_record_id,
            ])
            ->exists();
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
        return Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} commented {content}.', $params);
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
        $isOwner = (int)$this->content?->created_by === (int)$this->recipient->id;
        $params['space'] = $space?->displayName;

        if ($isOwner) {
            return $space
                ? Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} just commented your {content} in Space {space}', $params)
                : Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} just commented your {content}', $params);
        }

        return $space
            ? Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} commented {content} in Space {space}', $params)
            : Yii::t('CommentModule.notification', '{groupCount, plural, =1{{displayName}} other{{displayNames}}} commented {content}', $params);
    }
}
