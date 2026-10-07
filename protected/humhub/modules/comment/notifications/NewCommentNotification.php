<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\notifications;

use humhub\modules\comment\models\Comment;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\notification\components\ActiveQueryNotification;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationGroup;
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
    public static function group(): NotificationGroup
    {
        return NotificationGroup::social();
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
     * @inheritdoc
     */
    public function getGroupingQuery(): ?ActiveQueryNotification
    {
        if ($this->content === null) {
            return null;
        }

        return Notification::find()
            ->andWhere(['notification.class' => self::class])
            ->andWhere(['notification.content_id' => $this->content->id]);
    }

    /**
     * The comment itself.
     *
     * @inheritdoc
     */
    public function getMailContentRecord(): ?ContentOwner
    {
        return $this->sourceRecord instanceof Comment ? $this->sourceRecord : parent::getMailContentRecord();
    }

    /**
     * @inheritdoc
     */
    protected function getMessage(array $params): string
    {
        if ($this->groupCount > 1) {
            return Yii::t('CommentModule.notification', '{displayNames} commented {contentTitle}.', [
                'displayNames' => $params['displayNames'],
                'contentTitle' => $params['content'],
            ]);
        }

        return Yii::t('CommentModule.notification', '{displayName} commented {contentTitle}.', [
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
        if (!isset($params['content'])) {
            return parent::getMailSubject();
        }

        $space = $this->getSpace();
        $isOwner = (int)$this->content?->created_by === (int)$this->recipient->id;

        if ($this->groupCount > 1) {
            $names = ['displayNames' => $params['displayNames'], 'contentTitle' => $params['content']];

            if ($isOwner) {
                return $space
                    ? Yii::t('CommentModule.notification', '{displayNames} just commented your {contentTitle} in Space {space}', $names + ['space' => $space->displayName])
                    : Yii::t('CommentModule.notification', '{displayNames} just commented your {contentTitle}', $names);
            }

            return $space
                ? Yii::t('CommentModule.notification', '{displayNames} commented {contentTitle} in Space {space}', $names + ['space' => $space->displayName])
                : Yii::t('CommentModule.notification', '{displayNames} commented {contentTitle}', $names);
        }

        $name = ['displayName' => $params['displayName'], 'contentTitle' => $params['content']];

        if ($isOwner) {
            return $space
                ? Yii::t('CommentModule.notification', '{displayName} just commented your {contentTitle} in Space {space}', $name + ['space' => $space->displayName])
                : Yii::t('CommentModule.notification', '{displayName} just commented your {contentTitle}', $name);
        }

        return $space
            ? Yii::t('CommentModule.notification', '{displayName} commented {contentTitle} in Space {space}', $name + ['space' => $space->displayName])
            : Yii::t('CommentModule.notification', '{displayName} commented {contentTitle}', $name);
    }
}
