<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\widgets\menu\LeftNavigation;
use humhub\widgets\menu\MenuEntry;

/* @var $this View */
/* @var $menu LeftNavigation */
/* @var $entries MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('nav', $options) ?>
<?php if (!empty($menu->panelTitle)) : ?>
    <div class="card-header">
        <h2 class="card-title"><?= $menu->panelTitle ?></h2>
    </div>
<?php endif; ?>

<div class="list-group list-group-horizontal list-group-vertical-lg">
    <?php foreach ($entries as $entry): ?>
        <?= $entry->render(['class' => 'list-group-item']) ?>
    <?php endforeach; ?>
</div>
<?= Html::endTag('nav') ?>
