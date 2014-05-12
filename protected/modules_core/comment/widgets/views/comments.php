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


<div class="well well-small" style="<?php if (count($comments) == 0) {  echo 'display: none;'; } ?>" id="comment_<?php echo $id; ?>">
    <div class="comment" id="comments_area_<?php echo $id; ?>">
        <?php if ($isLimited): ?>
            <?php
            // Create an ajax link, which loads all comments upon request
            $showAllLabel = Yii::t('CommentModule.base', 'Show all {total} comments.', array('{total}' => $total));
            $reloadUrl = CHtml::normalizeUrl(Yii::app()->createUrl('comment/comment/show', array('model' => $modelName, 'id' => $modelId)));
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

    <?php /* BEGIN: Comment Create Form */ ?>
    <div id="comment_create_form_<?php echo $id; ?>">
        <?php echo CHtml::form("#"); ?>
        <?php echo CHtml::hiddenField('model', $modelName); ?>
        <?php echo CHtml::hiddenField('id', $modelId); ?>



        <?php echo CHtml::textArea("message", Yii::t('CommentModule.base', ""), array('id' => 'newCommentForm_' . $id, 'rows' => '1', 'class' => 'form-control autosize commentForm', 'placeholder' => 'Write a new comment...')); ?>
        <?php
        echo HHtml::ajaxSubmitButton(Yii::t('base', 'Post'), CHtml::normalizeUrl(array('/comment/comment/post')), array(
                'beforeSend' => "function() {
                $('#newCommentForm_" . $id . "').blur();
                }",
                'success' => "function(html) {
            $('#comments_area_" . $id . "').html(html);
            $('#newCommentForm_" . $id . "').val('').trigger('autosize.resize');
        }",
            ), array(
                'id' => "comment_create_post_" . $id,
                'class' => 'btn btn-small btn-primary',
                'style' => 'position: absolute; top: -3000px; left: -3000px;',
            )
        );
        ?>

        <?php echo Chtml::endForm(); ?>
    </div>
</div>
<?php /* END: Comment Create Form */ ?>

<script type="text/javascript">

    $('#newCommentForm_<?php echo $id; ?>').mention({
        searchUrl: '<?php echo Yii::app()->createAbsoluteUrl('user/search/json') ?>'
    });

    // Fire click event for comment button by typing enter
    $('#newCommentForm_<?php echo $id; ?>').keydown(function (event) {

        if (event.keyCode == 13) {


            if ($.fn.mention.defaults.stateUserList == false) {

                event.cancelBubble = true;
                event.returnValue = false;

                $('#comment_create_post_<?php echo $id; ?>').focus();
                $('#comment_create_post_<?php echo $id; ?>').click();

                // empty input
                $(this).val('');
            }


        }

        return event.returnValue;

    });

    // set the size for one row (Firefox)
    $('#newCommentForm_<?php echo $id; ?>').css({height: '36px'});

    // add autosize function to input
    $('.autosize').autosize();


</script>