<?php

use humhub\modules\comment\widgets\ShowMore;
use humhub\widgets\bootstrap\Link;

/* @var $text string */
/* @var $showMoreUrl string */
/* @var $type string */
/* @var $linkStyleClass string */
?>
<div class="showMore">
    <?php if ($type === ShowMore::TYPE_NEXT) : ?>
        <hr class="comment-separator">
    <?php endif; ?>
    <?= Link::withAction($text, 'comment.showMore', $showMoreUrl)->options(['data-type' => $type])->cssClass($linkStyleClass ?? '') ?>
</div>
