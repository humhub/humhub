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
    <a href="<?php echo $user->getUrl(); ?>" class="pull-left">
        <img class="media-object img-rounded" src="<?php echo $user->getProfileImage()->getUrl(); ?>" width="40"
             height="40" alt="40x40" data-src="holder.js/40x40" style="width: 40px; height: 40px;"/>
    </a>

    <div class="media-body">
        <h4 class="media-heading"><a href="<?php echo $user->getProfileUrl(); ?>"><?php echo $user->displayName; ?></a> <small><?php echo HHtml::timeago($comment->created_at); ?></small></h4>
        <span class="content">
            <?php
            print HHtml::enrichText($comment->message);
            ?>
        </span>

        <?php //echo CHtml::link(Yii::t('base', "Delete"), '#'); ?>

        <div class="wall-entry-controls">
            <?php
            if ($comment->canDelete()) {
                $deleteUrl = CHtml::normalizeUrl(array('//comment/comment/delete', 'model' => $comment->object_model, 'id' => $comment->object_id, 'cid' => $comment->id));
                echo HHtml::ajaxLink(Yii::t('base', 'Delete'), $deleteUrl, array(
                    'type' => 'POST',
                    'data' => array(Yii::app()->request->csrfTokenName => Yii::app()->request->csrfToken),
                    'success' => "function(html) { $('#comments_area_" . $comment->object_model . "_" . $comment->object_id . "').html(html); }",
                        ), array(
                    'id' => "comment_delete_link" . $comment->id
                        )
                );
                echo " - ";
            }
            ?>

            <?php Yii::app()->getController()->widget('application.modules_core.like.widgets.LikeLinkWidget', array('object' => $comment)); ?>
        </div>
    </div>
</div>
<hr>
