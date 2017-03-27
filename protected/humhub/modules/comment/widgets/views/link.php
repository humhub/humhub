<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>

<?php if ($mode == \humhub\modules\comment\widgets\CommentLink::MODE_POPUP): ?>
    <a href="<?= Url::to(['/comment/comment/show', 'contentModel' => $objectModel, 'contentId' => $objectId, 'mode' => 'popup']); ?>"
       class=""
       title="" data-target="#globalModal"
       data-original-title="Comments">Comments (<?= $this->context->getCommentsCount(); ?>)</a>
<?php else: ?>
    <?php
    if (Yii::$app->user->isGuest) {
        echo Html::a(Yii::t('CommentModule.widgets_views_link', "Comment"), Yii::$app->user->loginUrl, ['data-target' => '#globalModal'));
    } else {
        echo Html::a(Yii::t('CommentModule.widgets_views_link', "Comment"), "#", ['onClick' => "$('#comment_" . $id . "').show();$('#newCommentForm_" . $id . "_contenteditable').focus();return false;"]);
    }
    ?>
<?php endif; ?>