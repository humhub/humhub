<?php

use humhub\libs\Html;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\widgets\DropdownMenu;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $menu DropdownMenu */
/* @var $entries MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('div', $options) ?>
<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true">
    <?= $menu->label ?>
    <span class="caret"></span>
</button>

<ul class="dropdown-menu pull-right">
    <?php foreach ($entries as $entry) : ?>
        <li>
            <?= $entry->render() ?>
        </li>
    <?php endforeach; ?>
</ul>
<?= Html::endTag('div') ?>
