<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\comment\widgets;

use humhub\components\Widget;
use Yii;
use yii\helpers\Url;

/**
 * This widget is used to show a single comment.
 * It will used by the CommentsWidget and the CommentController to show comments.
 */
class Comment extends Widget
{

    /**
     * @var \humhub\modules\comment\models\Comment the comment
     */
    public $comment = null;

    /**
     * @var boolean indicator that comment has just changed
     */
    public $justEdited = false;

    /**
     * @var bool True to force show even blocked comment
     */
    public $showBlocked = false;

    /**
     * @var string Default style class of div wrapper around Comment block
     */
    public $defaultClass = 'media';

    /**
     * @var string Additional style class of div wrapper around Comment block
     */
    public $additionalClass = '';

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->isBlockedAuthor()
            ? $this->renderBlockedComment()
            : $this->renderComment();
    }

    /**
     * @return string
     */
    private function renderBlockedComment(): string
    {
        $loadBlockedCommentUrl = Url::to(['/comment/comment/load',
            'objectModel' => $this->comment->object_model,
            'objectId' => $this->comment->object_id,
            'id' => $this->comment->id,
            'showBlocked' => true,
        ]);

        return $this->render('commentBlockedUser', [
            'comment' => $this->comment,
            'loadBlockedCommentUrl' => $loadBlockedCommentUrl,
        ]);
    }

    private function renderComment(): string
    {
        $deleteUrl = Url::to(['/comment/comment/delete',
            'objectModel' => $this->comment->object_model, 'objectId' => $this->comment->object_id, 'id' => $this->comment->id]);
        $editUrl = Url::to(['/comment/comment/edit',
            'objectModel' => $this->comment->object_model, 'objectId' => $this->comment->object_id, 'id' => $this->comment->id]);
        $loadUrl = Url::to(['/comment/comment/load',
            'objectModel' => $this->comment->object_model, 'objectId' => $this->comment->object_id, 'id' => $this->comment->id]);

        return $this->render('comment', [
            'comment' => $this->comment,
            'user' => $this->comment->user,
            'createdAt' => $this->comment->created_at,
            'class' => trim($this->defaultClass . ' ' . $this->additionalClass),
        ]);
    }

    /**
     * Check if author of the Comment is blocked for the current User
     *
     * @return bool
     */
    private function isBlockedAuthor(): bool
    {
        if ($this->showBlocked) {
            return false;
        }

        if (Yii::$app->user->isGuest) {
            return false;
        }

        return Yii::$app->user->getIdentity()->isBlockedForUser($this->comment->createdBy);
    }

}
