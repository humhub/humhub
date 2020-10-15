<?php

use humhub\modules\ui\icon\widgets\Icon;

/* @var $this humhub\modules\ui\view\components\View */
?>
<li>
    <!-- load modal confirm widget -->
    <a  href="#" data-action-click="delete">
        <?= Icon::get('delete') ?> <?= Yii::t('ContentModule.base', 'Delete') ?>
    </a>
</li>
