<?php

/* @var $this humhub\components\View */
/* @var content humhub\modules\content\models\Content */
/* @var $toggleLink string */

?>
<li>
    <?php if($content->isPrivate()) :?>
        <a href="#"  class="makePublicLink" data-action-click="toggleVisibility" data-action-url="<?= $toggleLink ?>">
            <i class="fa fa-unlock makePublic"></i> <?= Yii::t('ContentModule.widgets_views_contentForm', 'Make public') ?>
        </a>
    <?php else: ?>
        <a href="#" class="makePriavteLink" data-action-click="toggleVisibility" data-action-url="<?= $toggleLink ?>">
            <i class="fa fa-lock makePrivate"></i> <?= Yii::t('ContentModule.widgets_views_contentForm', 'Make private') ?>
        </a>
    <?php endif; ?>
</li>
