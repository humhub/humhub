<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\widgets\menu\DropdownMenu;
use humhub\widgets\menu\MenuEntry;

/* @var $this View */
/* @var $menu DropdownMenu */
/* @var $entries MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('ul', $options) ?>
<?php foreach ($entries as $entry): ?>
    <li class="nav-item">
        <?= $entry->render(['class' => 'nav-link' . ($entry->getIsActive() ? ' active' : '')]) ?>
    </li>
<?php endforeach; ?>
<?= Html::endTag('ul') ?>
