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
<ul class="nav nav-tabs">
    <?php foreach ($entries as $entry): ?>
        <li class="nav-item">
            <?= $entry->render([
                'class' => 'nav-link' . ($entry->getIsActive() ? ' active' : ''),
            ]) ?>
        </li>
    <?php endforeach; ?>
</ul>
<?= Html::endTag('div') ?>
