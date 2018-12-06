<?php

/* @var $this \humhub\components\View */
/* @var $menu \humhub\modules\ui\menu\widgets\TabMenu */
/* @var $entries \humhub\modules\ui\menu\MenuEntry[] */
?>

<div class="tab-menu">
    <ul class="nav nav-tabs">
        <?php foreach ($entries as $entry): ?>
            <li <?php if ($entry->getIsActive()): ?>class="active"<?php endif; ?>>
                <?= $entry->renderLinkTag() ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
