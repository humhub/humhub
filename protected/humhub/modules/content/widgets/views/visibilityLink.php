<?php

use humhub\widgets\Icon;

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
            <?= Icon::get('lock-open')->class('makePublic') ?> <?= Yii::t('ContentModule.base', 'Change to "Public"') ?>
        </a>
    <?php else: ?>
        <a href="#"
           class="dropdown-item makePrivateLink"
           data-action-click="toggleVisibility"
           data-action-url="<?= $toggleLink ?>">
            <?= Icon::get('lock')->class('makePrivate') ?> <?= Yii::t('ContentModule.base', 'Change to "Private"') ?>
        </a>
    <?php endif; ?>
</li>
