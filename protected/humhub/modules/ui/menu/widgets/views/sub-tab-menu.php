<?php

/* @var $this \humhub\components\View */
/* @var $menu \humhub\modules\ui\menu\widgets\SubTabMenu */
/* @var $entries \humhub\modules\ui\menu\MenuEntry[] */
?>

<ul id="tabs" class="nav nav-tabs tab-sub-menu">
    <?php foreach ($entries as $entry): ?>
        <li>
            <?= $entry->renderLinkTag() ?>
        </li>
    <?php endforeach; ?>
</ul>
