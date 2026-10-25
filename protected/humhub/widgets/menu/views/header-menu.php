<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\widgets\Icon;
use humhub\widgets\menu\DropdownMenu;
use humhub\widgets\menu\MenuEntry;

/**
 * A dropdown menu as the ⋮ menu of a header (`humhub\widgets\PageHeader::menu()`,
 * `humhub\widgets\ContentCard`).
 *
 * @var $this View
 * @var $menu DropdownMenu
 * @var $entries MenuEntry[]
 * @var $options array
 */

$label = $menu->label === null ? Yii::t('base', 'More actions') : strip_tags($menu->label);
Html::addCssClass($options, 'dropdown');
?>
<?= Html::beginTag('div', $options) ?>
    <?= Html::button(Icon::get('dots-vertical'), [
        'class' => 'btn btn-ghost btn-icon-only',
        'data-bs-toggle' => 'dropdown',
        'aria-haspopup' => 'menu',
        'aria-expanded' => 'false',
        'aria-label' => $label,
        'title' => $label,
    ]) ?>
    <ul class="dropdown-menu dropdown-menu-end">
        <?php foreach ($entries as $entry) : ?>
            <li><?= $entry->render(['class' => 'dropdown-item']) ?></li>
        <?php endforeach; ?>
    </ul>
<?= Html::endTag('div') ?>
