<?php

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
?>


<?php if ($mode == \humhub\modules\comment\widgets\Link::MODE_POPUP): ?>
    <a href="<?php echo Url::to(['/comment/comment/show', 'contentModel' => $objectModel, 'contentId' => $objectId, 'mode' => 'popup']); ?>"
       class="" data-toggle="modal"
       title="" data-target="#globalModal"
       data-original-title="Comments">Comments (<?php echo $this->context->getCommentsCount(); ?>)</a>
<?php else: ?>
    <?php
    if (Yii::$app->user->isGuest) {
        echo Html::a(Yii::t('CommentModule.widgets_views_link', "Comment"), Url::to([Yii::$app->user->loginUrl]), array('data-target' => '#globalModal', 'data-toggle' => 'modal'));
    } else {
        echo Html::a(Yii::t('CommentModule.widgets_views_link', "Comment"), "#", array('onClick' => "$('#comment_" . $id . "').show();$('#newCommentForm_" . $id . "_contenteditable').focus();return false;"));
    }
    ?>
<?php endif; ?>