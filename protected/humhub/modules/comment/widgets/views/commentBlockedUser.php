<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\comment\models\Comment;
use humhub\modules\ui\view\components\View;
use humhub\modules\user\widgets\Image as UserImage;
use humhub\widgets\Button;

/* @var $this View */
/* @var $comment Comment */
/* @var $loadBlockedCommentUrl string */
?>

<div class="media comment-blocked-user" id="comment_<?= $comment->id; ?>"
     data-action-component="comment.Comment">

    <hr class="comment-separator">

    <?= UserImage::widget(['user' => $comment->user, 'width' => 25, 'htmlOptions' => ['class' => 'pull-left', 'data-contentcontainer-id' => $comment->user->contentcontainer_id]]); ?>
    <?= Yii::t('CommentModule.base', 'Comment of blocked user.') ?>
    <?= Button::asLink(Yii::t('CommentModule.base', 'Show'))->action('showBlocked', $loadBlockedCommentUrl)->xs()->cssClass('text-primary') ?>
</div>
