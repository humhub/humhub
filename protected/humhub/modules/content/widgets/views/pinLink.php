<?php
/* @var $this View */
/* @var $isPinned bool */
/* @var $unpinUrl string */

/* @var $pinUrl string */

use humhub\widgets\bootstrap\Button;
use yii\web\View;

?>
<li>
    <?php if ($isPinned): ?>
        <?= Button::asLink(Yii::t('ContentModule.base', 'Unpin'))
            ->action('unpin', $unpinUrl)
            ->cssClass('dropdown-item')
            ->icon('map-pin') ?>
    <?php else: ?>
        <?= Button::asLink(Yii::t('ContentModule.base', 'Pin to top'))
            ->action('pin', $pinUrl)
            ->cssClass('dropdown-item')
            ->icon('map-pin') ?>
    <?php endif; ?>
</li>
