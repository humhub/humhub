<?php

/* @var $this \humhub\components\View */
/* @var $content humhub\modules\content\models\Content */
/* @var $toggleLink string */
?>

<li>
    <?php if ($content->isPrivate()) : ?>
        <a href="#"
           class="dropdown-item makePublicLink"
           data-action-click="toggleVisibility"
           data-action-url="<?= $toggleLink ?>">
            <i class="fa fa-unlock makePublic"></i> <?= Yii::t('ContentModule.base', 'Change to "Public"') ?>
        </a>
    <?php else: ?>
        <a href="#"
           class="dropdown-item makePrivateLink"
           data-action-click="toggleVisibility"
           data-action-url="<?= $toggleLink ?>">
            <i class="fa fa-lock makePrivate"></i> <?= Yii::t('ContentModule.base', 'Change to "Private"') ?>
        </a>
    <?php endif; ?>
</li>
