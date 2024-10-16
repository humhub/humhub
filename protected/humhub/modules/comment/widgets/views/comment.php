<?php

use humhub\helpers\Html;
use humhub\modules\comment\Module;
use humhub\modules\comment\widgets\CommentControls;
use humhub\modules\comment\widgets\CommentEntryLinks;
use humhub\modules\comment\widgets\Comments;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\content\widgets\UpdatedIcon;
use humhub\modules\file\widgets\ShowFiles;
use humhub\modules\ui\view\components\View;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\widgets\TimeAgo;

/* @var $this View */
/* @var $comment \humhub\modules\comment\models\Comment */
/* @var $user \humhub\modules\user\models\User */
/* @var $deleteUrl string */
/* @var $editUrl string */
/* @var $loadUrl string */
/* @var $createdAt string */
/* @var $updatedAt string */
/* @var $class string */

/** @var Module $module */
$module = Yii::$app->getModule('comment');

?>

<hr class="comment-separator">

<div class="<?= $class ?>" id="comment_<?= $comment->id ?>"
     data-action-component="comment.Comment">

    <?= CommentControls::widget(['comment' => $comment]) ?>

    <div class="flex-shrink-0 comment-header-image">
        <?= UserImage::widget(['user' => $user, 'width' => 25, 'htmlOptions' => ['data-contentcontainer-id' => $user->contentcontainer_id]]) ?>
    </div>

    <div class="flex-grow-1">
        <h4 class="comment-heading">
            <?= Html::containerLink($user) ?>
            <small>&middot <?= TimeAgo::widget(['timestamp' => $createdAt]) ?>
                <?php if ($comment->isUpdated()): ?>
                    &middot <?= UpdatedIcon::getByDated($comment->updated_at) ?>
                <?php endif; ?>
            </small>
        </h4>

        <!-- class comment_edit_content required since v1.2 -->
        <div class="content comment_edit_content" id="comment_editarea_<?= $comment->id; ?>">
            <div id="comment-message-<?= $comment->id; ?>" class="comment-message" data-ui-markdown data-ui-show-more
                 data-read-more-text="<?= Yii::t('CommentModule.base', 'Read full comment...') ?>">
                <?= RichText::output($comment->message) ?>
            </div>
            <?= ShowFiles::widget(['object' => $comment]); ?>
        </div>

        <div class="wall-entry-controls">
            <?= CommentEntryLinks::widget(['object' => $comment]); ?>
        </div>

        <div class="nested-comments-root">
            <?= Comments::widget(['object' => $comment]); ?>
        </div>
    </div>
</div>
