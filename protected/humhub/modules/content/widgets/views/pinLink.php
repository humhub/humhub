<?php
/* @var $this View */
/* @var $isPinned bool */
/* @var $unpinUrl string */

/* @var $pinUrl string */

use humhub\widgets\bootstrap\Link;
use yii\web\View;

?>
<li>
    <?php if ($isPinned): ?>
        <?= Link::to(Yii::t('ContentModule.base', 'Unpin'))
            ->action('unpin', $unpinUrl)
            ->cssClass('dropdown-item')
            ->icon('map-pin') ?>
    <?php else: ?>
        <?= Link::to(Yii::t('ContentModule.base', 'Pin to top'))
            ->action('pin', $pinUrl)
            ->cssClass('dropdown-item')
            ->icon('map-pin') ?>
    <?php endif; ?>
</li>
