<?php

use yii\helpers\Html;
use yii\helpers\Url;
use humhub\widgets\AjaxButton;
?>


<?php
$canWrite = $comment->canWrite();
$canDelete = $comment->canDelete();
?>

<div class="media" id="comment_<?php echo $comment->id; ?>">
    <?php if ($canWrite || $canDelete) : ?>

        <ul class="nav nav-pills preferences">
            <li class="dropdown ">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-angle-down"></i></a>

                <ul class="dropdown-menu pull-right">

                    <?php if ($canWrite): ?>
                        <li>
                            <?php
                            echo AjaxButton::widget([
                                'label' => '<i class="fa fa-pencil"></i> ' . Yii::t('CommentModule.widgets_views_showComment', 'Edit'),
                                'ajaxOptions' => [
                                    'type' => 'POST',
                                    'success' => new yii\web\JsExpression("function(html){ $('.preferences .dropdown').removeClass('open'); $('#comment_editarea_" . $comment->id . "').replaceWith(html); $('#comment_input_" . $comment->id . "_contenteditable').focus(); }"),
                                    'url' => Url::to(['/comment/comment/edit', 'contentModel' => $comment->object_model, 'contentId' => $comment->object_id, 'id' => $comment->id]),
                                ],
                                'tag' => 'a'
                            ]);
                            ?>

                        </li>
                    <?php endif; ?>

                    <?php if ($canDelete): ?>
                        <li>

                            <!-- load modal confirm widget -->
                            <?php
                            echo humhub\widgets\ModalConfirm::widget(array(
                                'uniqueID' => 'modal_commentdelete_' . $comment->id,
                                'linkOutput' => 'a',
                                'title' => Yii::t('CommentModule.widgets_views_showComment', '<strong>Confirm</strong> comment deleting'),
                                'message' => Yii::t('CommentModule.widgets_views_showComment', 'Do you really want to delete this comment?'),
                                'buttonTrue' => Yii::t('CommentModule.widgets_views_showComment', 'Delete'),
                                'buttonFalse' => Yii::t('CommentModule.widgets_views_showComment', 'Cancel'),
                                'linkContent' => '<i class="fa fa-trash-o"></i> ' . Yii::t('CommentModule.widgets_views_showComment', 'Delete'),
                                'linkHref' => Url::to(["/comment/comment/delete", 'contentModel' => $comment->object_model, 'contentId' => $comment->object_id, 'id' => $comment->id]),
                                'confirmJS' => "function(html) { $('#comment_" . $comment->id . "').slideUp(); }"
                            ));
                            ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>
        </ul>

    <?php endif; ?>

    <a href="<?php echo $user->getUrl(); ?>" class="pull-left">
        <img class="media-object img-rounded user-image user-<?php echo $user->guid; ?>" src="<?php echo $user->getProfileImage()->getUrl(); ?>"
             width="40"
             height="40" alt="40x40" data-src="holder.js/40x40" style="width: 40px; height: 40px;"/>
    </a>

    <div class="media-body">
        <h4 class="media-heading"><a href="<?php echo $user->getUrl(); ?>"><?php echo Html::encode($user->displayName); ?></a>
            <small><?php echo \humhub\widgets\TimeAgo::widget(['timestamp' => $comment->created_at]); ?>
                <?php if ($comment->updated_at != "" && $comment->created_at != $comment->updated_at): ?>
                    (<?php echo Yii::t('CommentModule.widgets_views_showComment', 'Updated :timeago', array(':timeago' => \humhub\widgets\TimeAgo::widget(['timestamp' => $comment->updated_at]))); ?>)
                <?php endif; ?>
            </small>
        </h4>


        <div class="content" id="comment_editarea_<?php echo $comment->id; ?>">
            <span id="comment-message-<?php echo $comment->id; ?>"><?php echo humhub\widgets\RichText::widget(['text' => $comment->message, 'record' => $comment]); ?></span>
            <?php echo humhub\modules\file\widgets\ShowFiles::widget(array('object' => $comment)); ?>
        </div>

        <div class="wall-entry-controls">
            <?php echo humhub\modules\like\widgets\LikeLink::widget(array('object' => $comment)); ?>
        </div>
    </div>
    <hr>
</div>

<?php if ($justEdited): ?>
    <script type="text/javascript">
        $('#comment-message-<?php echo $comment->id; ?>').addClass('highlight');
        $('#comment-message-<?php echo $comment->id; ?>').delay(200).animate({backgroundColor: 'transparent'}, 1000);
    </script>
<?php endif; ?>

<?php if ($comment->canDelete()) : ?>
    <script type="text/javascript">
        $('.comment .media').mouseover(function () {
            // find dropdown menu
            var element = $(this).find('.preferences');
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
