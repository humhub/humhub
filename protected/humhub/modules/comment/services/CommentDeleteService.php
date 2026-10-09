<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\services;

use humhub\modules\comment\models\Comment;
use humhub\modules\comment\notifications\CommentDeletedNotification;
use humhub\modules\content\helpers\ContentHelper;
use Yii;

/**
 * Deletes a comment, optionally notifying its author with a reason — the moderation
 * flow behind the admin-delete modal. Extracted from the comment controller so the
 * REST API (and any future caller) shares the exact same behavior.
 *
 * @since 1.20
 */
class CommentDeleteService
{
    public function __construct(private readonly Comment $comment)
    {
    }

    /**
     * Deletes the comment (including its replies, see {@see Comment::beforeDelete()}).
     * With `$notify`, the author receives a {@see CommentDeletedNotification} web notification
     * carrying a preview of the removed text and the optional `$reason`.
     *
     * Authorization ({@see Comment::canDelete()}) is the caller's responsibility.
     */
    public function delete(bool $notify = false, string $reason = ''): bool
    {
        if ($notify) {
            CommentDeletedNotification::send(
                $this->comment->createdBy,
                originator: Yii::$app->user->getIdentity(),
                payload: [
                    'commentText' => ContentHelper::getContentPlainTextPreview($this->comment, 30),
                    'reason' => $reason,
                ],
            );
        }

        return (bool)$this->comment->delete();
    }
}
