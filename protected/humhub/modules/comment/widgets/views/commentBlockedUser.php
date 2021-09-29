<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\comment\models\Comment;
use humhub\modules\ui\view\components\View;
use humhub\widgets\Button;

/* @var $this View */
/* @var $comment Comment */
/* @var $loadBlockedCommentUrl string */
?>

<div class="media" id="comment_<?= $comment->id; ?>"
     data-action-component="comment.Comment">

    <hr class="comment-separator">

    <?= Yii::t('CommentModule.base', 'Comment of blocked user') ?>
    <?= Button::warning(Yii::t('CommentModule.base', 'Show'))->action('showBlocked', $loadBlockedCommentUrl)->xs() ?>
</div>
