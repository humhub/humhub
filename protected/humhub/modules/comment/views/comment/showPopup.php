<?php

use humhub\components\View;
use humhub\modules\comment\models\Comment;
use humhub\modules\comment\widgets\Comments;
use humhub\modules\content\models\Content;
use humhub\widgets\modal\Modal;

/* @var $this View */
/* @var $content Content */
/* @var $parentComment ?Comment */
?>

<?php Modal::beginDialog([
    'title' => Yii::t('CommentModule.base', 'Comments'),
    'bodyOptions' => [
        'class' => ['comment-container', 'comment-modal-body'],
        'style' => 'margin-top: 0;',
    ],
]) ?>

    <?= Comments::widget(['content' => $content, 'parentComment' => $parentComment]) ?>

<?php Modal::endDialog() ?>
