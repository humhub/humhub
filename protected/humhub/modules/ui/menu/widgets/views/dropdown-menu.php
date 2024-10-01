<?php

use humhub\helpers\Html;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\widgets\DropdownMenu;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $menu DropdownMenu */
/* @var $entries MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('div', $options) ?>
<button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true">
    <?= $menu->label ?>
</button>

<ul class="dropdown-menu float-end">
    <?php foreach ($entries as $entry) : ?>
        <li>
            <?= $entry->render(['class' => 'dropdown-item']) ?>
        </li>
    <?php endforeach; ?>
</ul>
<?= Html::endTag('div') ?>
