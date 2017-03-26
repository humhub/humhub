<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>

<?php if ($mode == \humhub\modules\comment\widgets\CommentLink::MODE_POPUP): ?>
    <?php $url = Url::to(['/comment/comment/show', 'contentModel' => $objectModel, 'contentId' => $objectId, 'mode' => 'popup']); ?>
    <a href="#" data-action-click="ui.modal.load" data-action-url="<?= $url ?>">
        <?= Yii::t('CommentModule.widgets_views_link', "Comment").'('.$this->context->getCommentsCount().')' ?>
    </a>
<?php elseif(Yii::$app->user->isGuest): ?>
    <?= Html::a(Yii::t('CommentModule.widgets_views_link', "Comment"), Yii::$app->user->loginUrl, ['data-target' => '#globalModal']) ?>
<?php else : ?>
    <?= Html::a(Yii::t('CommentModule.widgets_views_link', "Comment"), "#",['onClick' => "$('#comment_" . $id . "').show();$('#newCommentForm_" . $id . "_contenteditable').focus();return false;"]); ?>
<?php endif; ?>