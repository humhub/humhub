<?php
/* @var $this View */
/* @var $isPinned bool */
/* @var $unpinUrl string */

/* @var $pinUrl string */

use humhub\modules\ui\icon\widgets\Icon;
use yii\web\View;

?>
<li>
    <?php if ($isPinned): ?>
        <a href="#"
           class="dropdown-item "
           data-action-click="unpin"
           data-action-url="<?= $unpinUrl ?>">
            <?= Icon::get('map-pin') ?> <?php echo Yii::t('ContentModule.base', 'Unpin'); ?>
        </a>
    <?php else: ?>
        <a href="#"
           class="dropdown-item "
           data-action-click="pin"
           data-action-url="<?= $pinUrl ?>">
            <?= Icon::get('map-pin') ?> <?php echo Yii::t('ContentModule.base', 'Pin to top'); ?>
        </a>
    <?php endif; ?>
</li>
