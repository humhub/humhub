<?php

/* @var $this humhub\modules\ui\view\components\View */
/* @var content humhub\modules\content\models\Content */
/* @var $toggleLink string */

?>
<li>
    <?php if($content->isPrivate()) :?>
        <a href="#"  class="makePublicLink" data-action-click="toggleVisibility" data-action-url="<?= $toggleLink ?>">
            <i class="fa fa-unlock makePublic"></i> <?= Yii::t('ContentModule.base', 'Make public') ?>
        </a>
    <?php else: ?>
        <a href="#" class="makePriavteLink" data-action-click="toggleVisibility" data-action-url="<?= $toggleLink ?>">
            <i class="fa fa-lock makePrivate"></i> <?= Yii::t('ContentModule.base', 'Make private') ?>
        </a>
    <?php endif; ?>
</li>
