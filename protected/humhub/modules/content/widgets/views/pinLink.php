<?php
/* @var $this View */
/* @var $isPinned bool */
/* @var $unpinUrl string */

/* @var $pinUrl string */

use yii\web\View;

?>
<li>
    <?php if ($isPinned): ?>
        <a href="#" data-action-click="unpin" data-action-url="<?= $unpinUrl ?>">
            <i class="fa fa-map-pin"></i> <?php echo Yii::t('ContentModule.base', 'Unpin'); ?>
        </a>
    <?php else: ?>
        <a href="#" data-action-click="pin" data-action-url="<?= $pinUrl ?>">
            <i class="fa fa-map-pin"></i> <?php echo Yii::t('ContentModule.base', 'Pin to top'); ?>
        </a>
    <?php endif; ?>
</li>
