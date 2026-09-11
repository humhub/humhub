<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\menu\DropdownMenu;
use humhub\widgets\menu\MenuEntry;

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
        ->options([
            'data-bs-toggle' => 'dropdown',
            'aria-label' => $menu->label ?? Yii::t('base', 'Actions'),
        ])
        ->loader(false) ?>

    <ul class="dropdown-menu dropdown-menu-end">
        <?php foreach ($entries as $entry) : ?>
            <li>
                <?= $entry->render(['class' => 'dropdown-item']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?= Html::endTag('div') ?>
