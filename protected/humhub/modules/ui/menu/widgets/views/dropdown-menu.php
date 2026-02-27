<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\widgets\DropdownMenu;
use humhub\widgets\bootstrap\Button;

/* @var $this View */
/* @var $menu DropdownMenu */
/* @var $entries MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('div', $options) ?>
    <?= Button::light($menu->label)
        ->encodeLabel($menu->encodeLabel)
        ->icon($menu->icon)
        ->cssClass('dropdown-toggle')
        ->options(['data-bs-toggle' => 'dropdown'])
        ->loader(false) ?>

    <ul class="dropdown-menu dropdown-menu-end">
        <?php foreach ($entries as $entry) : ?>
            <li>
                <?= $entry->render(['class' => 'dropdown-item']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?= Html::endTag('div') ?>
