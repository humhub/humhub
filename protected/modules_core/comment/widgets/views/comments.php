<?php
/**
 * This view represents the initial view of comments inside the wall.
 * Inital means, that not all comments are display, just the last 2.
 *
 * @property Array $comments a list of comments to display
 * @property String $modelName The Model (e.g. Post) which the comments belongs to
 * @property Int $modelId The Primary Key of the Model which the comments belongs to
 * @property Int $total the number of total existing comments for this object
 * @property Boolean $isLimited indicates if not all comments are shown
 * @property String $id is a unique Id on Model and PK e.g. (Post_1)
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
?>


<div class="well well-small comment-container" style="display: none;" id="comment_<?php echo $id; ?>">
    <div class="comment <?php if (Yii::app()->user->isGuest): ?>guest-mode<?php endif; ?>" id="comments_area_<?php echo $id; ?>">
        <?php if ($isLimited): ?>
            <?php
            // Create an ajax link, which loads all comments upon request
            $showAllLabel = Yii::t('CommentModule.widgets_views_comments', 'Show all {total} comments.', array('{total}' => $total));
            $reloadUrl = CHtml::normalizeUrl(Yii::app()->createUrl('comment/comment/show', array('contentModel' => $modelName, 'contentId' => $modelId)));
            echo HHtml::ajaxLink($showAllLabel, $reloadUrl, array(
                'success' => "function(html) { $('#comments_area_" . $id . "').html(html); }",
                    ), array('id' => $id . "_showAllLink", 'class' => 'show show-all-link'));
            ?>
            <hr>
        <?php endif; ?>

        <?php foreach ($comments as $comment) : ?>
            <?php $this->widget('application.modules_core.comment.widgets.ShowCommentWidget', array('comment' => $comment)); ?>
        <?php endforeach; ?>
    </div>

    <?php $this->widget('application.modules_core.comment.widgets.CommentFormWidget', array('object' => $object)); ?>

</div>
<?php /* END: Comment Create Form */ ?>

<script type="text/javascript">

<?php if (count($comments) != 0) { ?>
        // make comments visible at this point to fixing autoresizing issue for textareas in Firefox
        $('#comment_<?php echo $id; ?>').show();
<?php } ?>



</script>