<?php
/**
 * This view is used by the CommentLinkWidget to inject a link to the
 * Wall Entry Controls.
 *
 * The primary goal is to show the new comment input when clicking it.
 * The Input Form is defined in comments.php
 *
 * @property String $id is a unique Id on Model and PK e.g. (Post_1)
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
?>

<?php if ($mode == CommentLinkWidget::MODE_POPUP): ?>
    <a href="<?php echo $this->createUrl('//comment/comment/show', array('contentModel' => $objectModel, 'contentId' => $objectId, 'mode' => 'popup')); ?>"
       class="" data-toggle="modal"
       title="" data-target="#globalModal"
       data-original-title="Comments">Comments (<?php echo $this->getCommentsCount(); ?>)</a>
<?php else: ?>
    <?php
    if (Yii::app()->user->isGuest) {
        echo CHtml::link(Yii::t('CommentModule.widgets_views_link', "Comment"), Yii::app()->user->loginUrl, array('data-target' => '#globalModal', 'data-toggle' => 'modal'));
    } else {
        echo CHtml::link(Yii::t('CommentModule.widgets_views_link', "Comment"), "#", array('onClick' => "$('#comment_" . $id . "').show();$('#newCommentForm_" . $id . "_contenteditable').focus();return false;"));
    }
    ?>
<?php endif; ?>