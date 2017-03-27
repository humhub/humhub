<?php

use yii\helpers\Html;
use yii\helpers\Url;

$commentCount = $this->context->getCommentsCount();
$hasComments = ($commentCount > 0);
$commentCountSpan = Html::tag('span', ' ('.$commentCount.')', [
    'class' => 'comment-count',
    'data-count' => $commentCount,
    'style' => ($hasComments) ? null : 'display:none'
]);

?>

<?php if ($mode == \humhub\modules\comment\widgets\CommentLink::MODE_POPUP): ?>
    <?php $url = Url::to(['/comment/comment/show', 'contentModel' => $objectModel, 'contentId' => $objectId, 'mode' => 'popup']); ?>
    <a href="#" data-action-click="ui.modal.load" data-action-url="<?= $url ?>">
        <?= Yii::t('CommentModule.widgets_views_link', "Comment").'('.$this->context->getCommentsCount().')' ?>
    </a>
<?php elseif(Yii::$app->user->isGuest): ?>
    <?= Html::a(Yii::t('CommentModule.widgets_views_link', "Comment").$commentCountSpan, Yii::$app->user->loginUrl, ['data-target' => '#globalModal']) ?>
<?php else : ?>
    <?= Html::a(Yii::t('CommentModule.widgets_views_link', "Comment").$commentCountSpan, "#",['onClick' => "$('#comment_" . $id . "').slideToggle('fast');$('#newCommentForm_" . $id . "').focus();return false;"]); ?>
<?php endif; ?>