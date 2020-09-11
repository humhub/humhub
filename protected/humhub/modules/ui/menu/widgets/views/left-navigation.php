<?php

use humhub\libs\Html;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $menu \humhub\modules\ui\menu\widgets\LeftNavigation */
/* @var $entries \humhub\modules\ui\menu\MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('div', $options) ?>
    <?php if (!empty($menu->panelTitle)) : ?>
        <div class="panel-heading"><?= $menu->panelTitle; ?></div>
    <?php endif; ?>

    <div class="list-group">
        <?php foreach ($entries as $entry): ?>
            <?= $entry->render(['class' => 'list-group-item']) ?>
        <?php endforeach; ?>
    </div>
<?= Html::endTag('div') ?>
