<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>


<?php
$canWrite = $comment->canWrite();
$canDelete = $comment->canDelete();
?>

<div class="media" id="comment_<?= $comment->id; ?>" 
     data-action-component="comment.Comment" 
     data-content-delete-url="<?= Url::to(["/comment/comment/delete", 'contentModel' => $comment->object_model, 'contentId' => $comment->object_id, 'id' => $comment->id]) ?>">
    <?php if ($canWrite || $canDelete) : ?>
        <div class="comment-entry-loader pull-right"></div>
        <ul class="nav nav-pills preferences">
            <li class="dropdown ">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-angle-down"></i></a>

                <ul class="dropdown-menu pull-right">
                    <?php if ($canWrite): ?>
                        <li>
                            <a href="#" class="comment-edit-link" data-action-click="edit" data-action-url="<?= Url::to(['/comment/comment/edit', 'contentModel' => $comment->object_model, 'contentId' => $comment->object_id, 'id' => $comment->id]) ?>">
                                <i class="fa fa-pencil"></i> <?= Yii::t('CommentModule.widgets_views_showComment', 'Edit') ?>
                            </a>
                            <a href="#" class="comment-cancel-edit-link" data-action-click="cancelEdit" data-action-url="<?= Url::to(['/comment/comment/load', 'contentModel' => $comment->object_model, 'contentId' => $comment->object_id, 'id' => $comment->id]) ?>" style="display:none;">
                                <i class="fa fa-pencil"></i> <?= Yii::t('CommentModule.widgets_views_showComment', 'Cancel Edit') ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($canDelete): ?>
                        <li>
                            <a href="#" data-action-click="delete">
                                <i class="fa fa-trash-o"></i>  <?= Yii::t('CommentModule.widgets_views_showComment', 'Delete') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>
        </ul>
    <?php endif; ?>

    <a href="<?= $user->getUrl(); ?>" class="pull-left">
        <img class="media-object img-rounded user-image user-<?= $user->guid; ?>" 
             src="<?= $user->getProfileImage()->getUrl(); ?>"
             width="40"
             height="40" alt="40x40" data-src="holder.js/40x40" style="width: 40px; height: 40px;"/>
    </a>

    <div class="media-body">
        <h4 class="media-heading"><a href="<?= $user->getUrl(); ?>"><?= Html::encode($user->displayName); ?></a>
            <small><?= \humhub\widgets\TimeAgo::widget(['timestamp' => $comment->created_at]); ?>
                <?php if ($comment->updated_at != "" && $comment->created_at != $comment->updated_at): ?>
                    (<?= Yii::t('CommentModule.widgets_views_showComment', 'Updated :timeago', array(':timeago' => \humhub\widgets\TimeAgo::widget(['timestamp' => $comment->updated_at]))); ?>)
                <?php endif; ?>
            </small>
        </h4>

        <!-- Class comment_edit_content required since v1.2 -->
        <div class="content comment_edit_content" id="comment_editarea_<?= $comment->id; ?>">
            <span id="comment-message-<?= $comment->id; ?>" class="comment-message" data-ui-markdown>
                <?= humhub\widgets\RichText::widget(['text' => $comment->message, 'record' => $comment]); ?>
            </span>
            <?= humhub\modules\file\widgets\ShowFiles::widget(['object' => $comment]); ?>
        </div>

        <div class="wall-entry-controls">
            <?= humhub\modules\like\widgets\LikeLink::widget(['object' => $comment]); ?>
        </div>
    </div>
    <hr>
</div>
