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
<ul class="nav nav-tabs">
    <?php foreach ($entries as $entry): ?>
        <li <?php if ($entry->getIsActive()): ?>class="active"<?php endif; ?>>
            <?= $entry->render() ?>
        </li>
    <?php endforeach; ?>
</ul>
<?= Html::endTag('div') ?>
