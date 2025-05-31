<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\widgets\DropdownMenu;

/* @var $this View */
/* @var $menu DropdownMenu */
/* @var $entries MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('div', $options) ?>
<button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true">
    <?= $menu->label ?>
</button>

<ul class="dropdown-menu dropdown-menu-end">
    <?php foreach ($entries as $entry) : ?>
        <li>
            <?= $entry->render(['class' => 'dropdown-item']) ?>
        </li>
    <?php endforeach; ?>
</ul>
<?= Html::endTag('div') ?>
