<?php

use humhub\libs\Html;
use humhub\widgets\TimeAgo;
use humhub\widgets\RichText;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\modules\file\widgets\ShowFiles;
use humhub\modules\like\widgets\LikeLink;
?>

<div class="media" id="comment_<?= $comment->id; ?>"
     data-action-component="comment.Comment"
     data-content-delete-url="<?= $deleteUrl ?>">
         <?php if ($canWrite || $canDelete) : ?>
        <div class="comment-entry-loader pull-right"></div>
        <ul class="nav nav-pills preferences">
            <li class="dropdown ">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-angle-down"></i></a>

                <ul class="dropdown-menu pull-right">
                    <?php if ($canWrite): ?>
                        <li>
                            <a href="#" class="comment-edit-link" data-action-click="edit" data-action-url="<?= $editUrl ?>">
                                <i class="fa fa-pencil"></i> <?= Yii::t('CommentModule.widgets_views_showComment', 'Edit') ?>
                            </a>
                            <a href="#" class="comment-cancel-edit-link" data-action-click="cancelEdit" data-action-url="<?= $loadUrl ?>" style="display:none;">
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
    <?= UserImage::widget(['user' => $user, 'width' => 40, 'htmlOptions' => ['class' => 'pull-left']]); ?>
    <div>
        <div class="media-body">
            <h4 class="media-heading"><?= Html::containerLink($user); ?>
                <small><?= TimeAgo::widget(['timestamp' => $createdAt]); ?>
                    <?php if ($updatedAt !== null): ?>
                        &middot; <span class="tt" title="<?= Yii::$app->formatter->asDateTime($updatedAt); ?>"><?= Yii::t('ContentModule.base', 'Updated'); ?></span>
                    <?php endif; ?>
                </small>
            </h4>
        </div>
        <!-- class comment_edit_content required since v1.2 -->
        <div class="content comment_edit_content" id="comment_editarea_<?= $comment->id; ?>">
            <div id="comment-message-<?= $comment->id; ?>" class="comment-message" data-ui-markdown data-ui-show-more data-read-more-text="<?= Yii::t('CommentModule.widgets_views_showComment', 'Read full comment...') ?>">
                <?= RichText::widget(['text' => $comment->message, 'record' => $comment]); ?>
            </div>
            <?= ShowFiles::widget(['object' => $comment]); ?>
        </div>

        <div class="wall-entry-controls">
            <?= LikeLink::widget(['object' => $comment]); ?>
        </div>
    </div>
    <hr>
</div>
