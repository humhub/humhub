<?php
/**
 * This view represents the comment itself.
 *
 * @property User $user which created this comment
 * @property User $user which created this comment
 *
 * @package humhub.modules_core.comment
 * @since 0.5
 */
?>

<div class="media">

    <?php if ($comment->canDelete()) : ?>

        <ul class="nav nav-pills preferences">
            <li class="dropdown ">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-angle-down"></i></a>

                <ul class="dropdown-menu pull-right">
                    <li>
                        <?php echo HHtml::ajaxLink('<i class="fa fa-pencil"></i> Edit', Yii::app()->createAbsoluteUrl('//comment/comment/edit', array('id' => $comment->id)), array(
                            'success' => "js:function(html){ $('.preferences .dropdown').removeClass('open'); $('#comment_" . $comment->id . "').replaceWith(html); $('#comment_input_". $comment->id."_contenteditable').focus(); }"
                        )); ?>
                    </li>
                    <li>

                        <!-- load modal confirm widget -->
                        <?php $this->widget('application.widgets.ModalConfirmWidget', array(
                            'uniqueID' => 'modal_commentdelete_' . $comment->id,
                            'linkOutput' => 'a',
                            'title' => Yii::t('CommentModule.widgets_views_showComment', '<strong>Confirm</strong> comment deleting'),
                            'message' => Yii::t('CommentModule.widgets_views_showComment', 'Do you really want to delete this comment?'),
                            'buttonTrue' => Yii::t('CommentModule.widgets_views_showComment', 'Delete'),
                            'buttonFalse' => Yii::t('CommentModule.widgets_views_showComment', 'Cancel'),
                            'linkContent' => '<i class="fa fa-trash-o"></i> ' . Yii::t('CommentModule.widgets_views_showComment', 'Delete'),
                            'linkHref' => $this->createUrl("//comment/comment/delete", array('model' => $comment->object_model, 'id' => $comment->object_id, 'cid' => $comment->id)),
                            'confirmJS' => "function(html) { $('#comments_area_" . $comment->object_model . "_" . $comment->object_id . "').html(html); }"
                        ));

                        ?>

                    </li>
                </ul>
            </li>
        </ul>

    <?php endif; ?>

    <a href="<?php echo $user->getUrl(); ?>" class="pull-left">
        <img class="media-object img-rounded user-image" src="<?php echo $user->getProfileImage()->getUrl(); ?>"
             width="40"
             height="40" alt="40x40" data-src="holder.js/40x40" style="width: 40px; height: 40px;"/>
    </a>

    <div class="media-body">
        <h4 class="media-heading"><a href="<?php echo $user->getProfileUrl(); ?>"><?php echo $user->displayName; ?></a>
            <small><?php echo HHtml::timeago($comment->created_at); ?></small>
        </h4>
        <span class="content" id="comment_<?php echo $comment->id; ?>">
            <?php
            print HHtml::enrichText($comment->message);
            ?>

            <?php $this->widget('application.modules_core.file.widgets.ShowFilesWidget', array('object' => $comment)); ?>
            
        </span>

        <div class="wall-entry-controls">
            <?php Yii::app()->getController()->widget('application.modules_core.like.widgets.LikeLinkWidget', array('object' => $comment)); ?>
        </div>
    </div>
</div>
<hr>

<?php if ($comment->canDelete()) : ?>
    <script type="text/javascript">
        $('.comment .media').mouseover(function () {

            // find dropdown menu
            var element = $(this).find('.preferences');

            // show element
            element.show();

        })

        $('.comment .media').mouseout(function () {

            // find dropdown menu
            var element = $(this).find('.preferences');

            // hide dropdown if it's not open
            if (!element.find('li').hasClass('open')) {
                element.hide();
            }
        })
    </script>
<?php endif; ?>
