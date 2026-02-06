<?php

use humhub\modules\comment\widgets\ShowMore;
use humhub\widgets\bootstrap\Link;

/* @var $text string */
/* @var $showMoreUrl string */
/* @var $direction string */
/* @var $linkStyleClass string */
?>
<div class="showMore">
    <?php if ($direction === \humhub\modules\comment\services\CommentListService::LIST_DIR_NEXT) : ?>
        <hr class="comment-separator">
    <?php endif; ?>
    <?= Link::withAction($text, 'comment.showMore', $showMoreUrl)->options(['data-direction' => $direction])->cssClass($linkStyleClass ?? '') ?>
</div>
