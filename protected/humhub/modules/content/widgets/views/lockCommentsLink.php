<?php

use humhub\modules\content\models\Content;

/* @var $content Content */
/* @var $lockCommentsLink string */
/* @var $unlockCommentsLink string */
?>
<li>
    <?php if ($content->isLockedComments()) : ?>
        <a href="#" data-action-click="unlockComments" data-action-url="<?= $unlockCommentsLink ?>">
            <i class="fa fa-comment-o"></i> <?= Yii::t('ContentModule.base', 'Unlock comments') ?>
        </a>
    <?php else : ?>
        <a href="#" data-action-click="lockComments" data-action-url="<?= $lockCommentsLink ?>">
            <i class="fa fa-comment"></i> <?= Yii::t('ContentModule.base', 'Lock comments') ?>
        </a>
    <?php endif; ?>
</li>
