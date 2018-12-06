<?php

/* @var $this \humhub\components\View */
/* @var $menu \humhub\modules\ui\menu\widgets\LeftNavigation */
/* @var $entries \humhub\modules\ui\menu\MenuEntry[] */
?>

<div id="<?= $menu->id; ?>" data-menu-id="<?= $menu->id ?>" class="panel panel-default left-navigation">

    <?php if (!empty($menu->panelTitle)) : ?>
        <div class="panel-heading"><?= $menu->panelTitle; ?></div>
    <?php endif; ?>

    <div class="list-group">
        <?php foreach ($entries as $entry): ?>
            <?= $entry->renderLinkTag(['class' => 'list-group-item']) ?>
        <?php endforeach; ?>
    </div>

</div>
