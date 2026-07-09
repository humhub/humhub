<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\comment\widgets\Form;
use humhub\widgets\modal\Modal;

/* @var $this View */
/* @var $content \humhub\modules\content\models\Content */
/* @var $output string */
/* @var $id string */
?>

<?php Modal::beginDialog([
    'title' => Yii::t('CommentModule.base', 'Comments'),
    'bodyOptions' => [
        'class' => ['comment-container', 'comment-modal-body'],
        'style' => 'margin-top: 0;',
    ],
]) ?>

    <div id="userlist-content">
        <div class="bg-light p-3" id="comment_<?= $id ?>">
            <div class="comment" id="comments_area_<?= $id ?>">
                <?= $output ?>
            </div>
            <?= Form::widget(['content' => $content]); ?>
        </div>
    </div>

<?php Modal::endDialog() ?>

<script <?= Html::nonce() ?>>
    // scroll to top of list
    $(".comment-modal-body").animate({scrollTop: 0}, 200);

    <?php if(empty(trim((string) $output))) : ?>
    $('#comment_<?= $id ?>').find('.comment_create hr').hide();
    <?php endif; ?>
</script>
