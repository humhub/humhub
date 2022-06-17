<?php

use humhub\modules\comment\widgets\Form;
use humhub\modules\comment\widgets\Comment;
use humhub\libs\Html;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $object \humhub\modules\content\components\ContentActiveRecord */
/* @var $comments \humhub\modules\comment\models\Comment[] */
/* @var $currentCommentId int */
/* @var $id string unqiue object id */
/* @var $isLimited boolean */
/* @var $total int */
/* @var $showMoreUrl string */

?>
<div class="well well-small comment-container" style="display:none;" id="comment_<?= $id; ?>">
    <div class="comment <?php if (Yii::$app->user->isGuest): ?>guest-mode<?php endif; ?>"
         id="comments_area_<?= $id; ?>">

        <?php if ($isLimited): ?>
            <a href="#" class="show show-all-link" data-ui-loader data-action-click="comment.showAll"
               data-action-url="<?= $showMoreUrl ?>">
                <?= Yii::t('CommentModule.base', 'Show all {total} comments', ['{total}' => $total]) ?>
            </a>
            <hr class="comments-start-separator">
        <?php endif; ?>

        <?php foreach ($comments as $comment) : ?>
            <?= Comment::widget([
                'comment' => $comment,
                'additionalClass' => ($currentCommentId == $comment->id ? 'comment-current' : ''),
            ]); ?>
        <?php endforeach; ?>
    </div>

    <?= Form::widget(['object' => $object]); ?>
</div>

<script <?= Html::nonce() ?>>
<?php if (count($comments) != 0): ?>
    // make comments visible at this point to fixing autoresizing issue for textareas in Firefox
    $('#comment_<?= $id; ?>').show();
<?php endif; ?>
<?php if (!empty($currentCommentId)) : ?>
    $('#comment_<?= $currentCommentId ?>').get(0).scrollIntoView();
<?php endif; ?>
</script>
