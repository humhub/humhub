<?php
/**
 * This view represents a wall entry of a post.
 * Used by PostWidget to show Posts inside a wall.
 *
 * @property User $user the user which created this post
 * @property Post $post the current post
 *
 * @package humhub.modules.post
 * @since 0.5
 */
?>
<div class="panel panel-default post" id="post-<?php echo $post->id; ?>">
    <div class="panel-body">
        <?php $this->beginContent('application.modules_core.wall.views.wallLayout', array('object' => $post)); ?>
        <span id="post-content-<?php echo $post->id; ?>" style="overflow: hidden; margin-bottom: 5px;">
            <?php print HHtml::enrichText($post->message); ?>
        </span>
        <a class="more-link-post hidden" id="more-link-post-<?php echo $post->id; ?>" data-state="down"
           style="margin: 20px 0 20px 0;" href="javascript:showMore(<?php echo $post->id; ?>);"><i
                class="fa fa-arrow-down"></i> <?php echo Yii::t('PostModule.widgets_views_post', 'Read full post...'); ?>
        </a>
        <?php $this->endContent(); ?>
    </div>
</div>

<script type="text/javascript">

    <?php if ($justEdited): ?>
    $('#post-content-<?php echo $post->id; ?>').addClass('highlight');
    $('#post-content-<?php echo $post->id; ?>').delay(200).animate({backgroundColor: 'transparent'}, 1000);
    <?php endif; ?>

    $(document).ready(function () {

        // save the count of characters
        var _words = '<?php echo strlen(strip_tags(HHtml::enrichText($post->message))); ?>';


        var _postHeight = $('#post-content-<?php echo $post->id; ?>').outerHeight();


        /*        if (_words > 1100) {
         // show more-button
         $('#more-link-post-
        <?php echo $post->id; ?>').removeClass('hidden');
         // set limited height
         $('#post-content-
        <?php echo $post->id; ?>').css({'display': 'block', 'max-height': '310px'});
         }*/

        if (_postHeight > 310) {
            // show more-button
            $('#more-link-post-<?php echo $post->id; ?>').removeClass('hidden');
            // set limited height
            $('#post-content-<?php echo $post->id; ?>').css({'display': 'block', 'max-height': '310px'});
        }
    });

    function showMore(post_id) {

        // set current state
        var _state = $('#more-link-post-' + post_id).attr('data-state');

        if (_state == "down") {

            $('#post-content-' + post_id).css('max-height', '2000px');

            // set new link content
            $('#more-link-post-' + post_id).html('<i class="fa fa-arrow-up"></i> <?php echo CHtml::encode(Yii::t('PostModule.widgets_views_post', 'Collapse')); ?>');

            // update link state
            $('#more-link-post-' + post_id).attr('data-state', 'up');

        } else {
            // set back to limited length
            $('#post-content-' + post_id).css('max-height', '310px');

            // set new link content
            $('#more-link-post-' + post_id).html('<i class="fa fa-arrow-down"></i> <?php echo CHtml::encode(Yii::t('PostModule.widgets_views_post', 'Read full post...')); ?>');

            // update link state
            $('#more-link-post-' + post_id).attr('data-state', 'down');

        }

    }

</script>
