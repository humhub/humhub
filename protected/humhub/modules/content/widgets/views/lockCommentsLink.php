<?php

use humhub\modules\content\models\Content;
use humhub\widgets\Icon;

/* @var $content Content */
/* @var $lockCommentsLink string */
/* @var $unlockCommentsLink string */
?>
<li>
    <?php if ($content->isLockedComments()) : ?>
        <a href="#"
           class="dropdown-item "
           data-action-click="unlockComments"
           data-action-url="<?= $unlockCommentsLink ?>">
            <?= Icon::get('message-circle') ?> <?= Yii::t('ContentModule.base', 'Unlock comments') ?>
        </a>
    <?php else : ?>
        <a href="#"
           class="dropdown-item "
           data-action-click="lockComments"
           data-action-url="<?= $lockCommentsLink ?>">
            <?= Icon::get('message-circle-filled') ?> <?= Yii::t('ContentModule.base', 'Lock comments') ?>
        </a>
    <?php endif; ?>
</li>
