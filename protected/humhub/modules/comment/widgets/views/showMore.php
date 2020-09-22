<?php

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $moreCount int */
/* @var $showMoreUrl string */

?>
<div class="showMore">
    <a href="#" data-action-click="comment.showMore" data-action-url="<?= $showMoreUrl ?>">
        <?= Yii::t('CommentModule.base', "Show {count} more comments", ['{count}' => $moreCount]) ?>
    </a>
</div>
