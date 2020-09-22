<?php

use humhub\libs\Html;

/* @var $this \humhub\components\View */
/* @var $menu \humhub\modules\ui\menu\widgets\DropdownMenu */
/* @var $entries \humhub\modules\ui\menu\MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('ul', $options)?>
    <?php foreach ($entries as $entry): ?>
        <li <?php if ($entry->getIsActive()): ?>class="active"<?php endif; ?>>
            <?= $entry->render() ?>
        </li>
    <?php endforeach; ?>
<?= Html::endTag('ul')?>
