<?php

use humhub\helpers\Html;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\WidgetMenuEntry;
use humhub\modules\ui\menu\widgets\DropdownMenu;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $menu DropdownMenu */
/* @var $entries MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('ul', $options) ?>
<li class="nav-item dropdown">

    <?= Html::a('', '#', [
        'class' => 'nav-link dropdown-toggle',
        'data-bs-toggle' => 'dropdown',
        'aria-label' => Yii::t('base', 'Toggle stream entry menu'),
        'aria-haspopup' => 'true',
        'aria-expanded' => 'false',
        'role' => 'button',
    ]) ?>

    <ul class="dropdown-menu dropdown-menu-end">
        <?php foreach ($entries as $entry) : ?>
            <?php if ($entry instanceof WidgetMenuEntry) : ?>
                <?= $entry->render() ?>
            <?php else: ?>
                <li>
                    <?= $entry->render(['class' => 'dropdown-item']) ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</li>
<?= Html::endTag('ul') ?>
