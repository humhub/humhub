<?php

use humhub\helpers\Html;
use humhub\modules\comment\models\Comment as CommentModel;
use humhub\modules\comment\services\CommentListService;
use humhub\modules\comment\widgets\Comment;
use humhub\modules\comment\widgets\Form;
use humhub\modules\comment\widgets\ShowMore;
use humhub\modules\content\models\Content;

/* @var $content Content */
/* @var $parentComment ?CommentModel */
/* @var $comments CommentModel[] */
/* @var $highlightCommentId int */
/* @var $id string unique object id */
?>

<div class="bg-light p-2 mt-3 comment-container d-none" id="comment_<?= $id ?>">
    <div class="comment<?= Yii::$app->user->isGuest ? ' guest-mode' : '' ?>" id="comments_area_<?= $id ?>">
        <?= ShowMore::widget([
            'content' => $content,
            'parentComment' => $parentComment,
            'commentId' => isset($comments[0]) ? $comments[0]->id : null,
            'direction' => \humhub\modules\comment\services\CommentListService::LIST_DIR_PREV,
        ]) ?>

        <?php foreach ($comments as $comment) : ?>
            <hr class="comment-separator">
            <?= Comment::widget([
                'comment' => $comment,
                'additionalClass' => ($highlightCommentId == $comment->id ? 'comment-current' : ''),
            ]); ?>
        <?php endforeach ?>

        <?php if ($highlightCommentId && count($comments) > 1) : ?>
            <?= ShowMore::widget([
                'content' => $content,
                'parentComment' => $parentComment,
                'commentId' => $comments[count($comments) - 1]->id,
                'direction' => CommentListService::LIST_DIR_NEXT,
            ]) ?>
        <?php endif; ?>
    </div>

    <?= Form::widget(['content' => $content, 'parentComment' => $parentComment]); ?>
</div>

<script <?= Html::nonce() ?>>
    <?php if (count($comments) != 0) : ?>
        // make comments visible at this point to fixing autoresizing issue for textareas in Firefox
        $('#comment_<?= $id ?>').show();
    <?php endif; ?>
    <?php if (!empty($highlightCommentId)) : ?>
        $('#comment_<?= $highlightCommentId ?>').get(0).scrollIntoView();
    <?php endif; ?>
</script>
