<?php

use yii\helpers\Url;
?>
<div class="well well-small comment-container" style="display:none;" id="comment_<?= $id; ?>">
    <div class="comment <?php if (Yii::$app->user->isGuest): ?>guest-mode<?php endif; ?>" id="comments_area_<?= $id; ?>">
        <?php if ($isLimited): ?>
            <a href="#" class="show show-all-link" data-ui-loader data-action-click="comment.showAll" data-action-url="<?= Url::to(['/comment/comment/show', 'contentModel' => $modelName, 'contentId' => $modelId]) ?>">
                <?= Yii::t('CommentModule.widgets_views_comments', 'Show all {total} comments.', ['{total}' => $total]) ?>
            </a>
            <hr>
        <?php endif;
        ?>

        <?php foreach ($comments as $comment) : ?>
            <?= \humhub\modules\comment\widgets\Comment::widget(['comment' => $comment]); ?>
        <?php endforeach; ?>
    </div>

    <?= \humhub\modules\comment\widgets\Form::widget(['object' => $object]); ?>

</div>
<?php /* END: Comment Create Form */ ?>

<script>

<?php if (count($comments) != 0) { ?>
    // make comments visible at this point to fixing autoresizing issue for textareas in Firefox
    $('#comment_<?php echo $id; ?>').show();
<?php } ?>

</script>