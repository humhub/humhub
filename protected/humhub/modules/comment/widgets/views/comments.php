<?php

use yii\helpers\Url;
use humhub\widgets\AjaxButton;
?>
<div class="well well-small comment-container" style="display:none;" id="comment_<?= $id; ?>">
    <div class="comment <?php if (Yii::$app->user->isGuest): ?>guest-mode<?php endif; ?>" id="comments_area_<?= $id; ?>">
        <?php if ($isLimited): ?>
            <?= AjaxButton::widget([
                'label' => Yii::t('CommentModule.widgets_views_comments', 'Show all {total} comments.', array('{total}' => $total)),
                'ajaxOptions' => [
                    'type' => 'POST',
                    'success' => new yii\web\JsExpression("function(html) { $('#comments_area_" . $id . "').html(html); }"),
                    'url' => Url::to(['/comment/comment/show', 'contentModel' => $modelName, 'contentId' => $modelId]),
                ],
                'htmlOptions' => [
                    'id' => $id . "_showAllLink",
                    'class' => 'show show-all-link'
                ],
                'tag' => 'a'
            ]);
            ?>
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
        $('#comment_<?= $id; ?>').show();
<?php } ?>

</script>