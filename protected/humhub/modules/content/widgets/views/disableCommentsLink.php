<?php

use humhub\modules\content\models\Content;

/* @var $content Content */
/* @var $disableCommentsLink string */
/* @var $enableCommentsLink string */
?>
<li>
    <?php if ($content->isDisabledComments()) : ?>
        <a href="#" data-action-click="enableComments" data-action-url="<?= $enableCommentsLink ?>">
            <i class="fa fa-comment-o"></i> <?= Yii::t('ContentModule.base', 'Enable comments') ?>
        </a>
    <?php else : ?>
        <a href="#" data-action-click="disableComments" data-action-url="<?= $disableCommentsLink ?>">
            <i class="fa fa-comment"></i> <?= Yii::t('ContentModule.base', 'Disable comments') ?>
        </a>
    <?php endif; ?>
</li>
