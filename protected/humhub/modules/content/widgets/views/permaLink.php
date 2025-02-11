<?php

use humhub\modules\ui\icon\widgets\Icon;

/* @var $this \humhub\components\View */
/* @var $permaLink string */
?>

<li>
    <a href="#"
       class="dropdown-item"
       data-action-click="content.permalink"
       data-content-permalink="<?= $permaLink ?>">
        <?= Icon::get('link') ?><?= Yii::t('ContentModule.base', 'Permalink') ?>
    </a>
</li>
