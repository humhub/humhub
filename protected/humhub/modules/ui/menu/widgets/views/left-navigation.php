<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\widgets\LeftNavigation;

/* @var $this View */
/* @var $menu LeftNavigation */
/* @var $entries MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('nav', $options) ?>
<?php if (!empty($menu->panelTitle)) : ?>
    <h2 class="panel-heading"><?= $menu->panelTitle ?></h2>
<?php endif; ?>

<div class="list-group list-group-horizontal list-group-vertical-lg">
    <?php foreach ($entries as $entry): ?>
        <?= $entry->render(['class' => 'list-group-item']) ?>
    <?php endforeach; ?>
</div>
<?= Html::endTag('nav') ?>
