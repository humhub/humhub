<?php

use humhub\libs\Html;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $menu \humhub\modules\ui\menu\widgets\DropdownMenu */
/* @var $entries \humhub\modules\ui\menu\MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('div', $options)?>
    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true">
        <?= $menu->label ?>
        <span class="caret"></span>
   </button>

    <ul class="dropdown-menu float-end">
        <?php foreach ($entries as $entry) : ?>
            <li class="dropdown-item">
                <?= $entry->render() ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?= Html::endTag('div')?>
