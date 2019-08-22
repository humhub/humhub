<?php

use humhub\libs\Html;

/* @var $this \humhub\components\View */
/* @var $menu \humhub\modules\ui\menu\widgets\DropdownMenu */
/* @var $entries \humhub\modules\ui\menu\MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('div', $options)?>
    <ul class="nav nav-tabs">
        <?php foreach ($entries as $entry): ?>
            <li <?php if ($entry->getIsActive()): ?>class="active"<?php endif; ?>>
                <?= $entry->render() ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?= Html::endTag('div')?>
