<?php

use humhub\libs\Html;
use humhub\modules\content\widgets\LegacyWallEntryControlLink;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\widgets\DropdownMenu;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $menu DropdownMenu */
/* @var $entries MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('ul', $options) ?>
<li class="dropdown ">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#"
       aria-label="<?= Yii::t('base', 'Toggle stream entry menu'); ?>" aria-haspopup="true">
        <?= Icon::get('dropdownToggle') ?>
    </a>

    <ul class="dropdown-menu pull-right">
        <?php foreach ($entries as $entry) : ?>
            <?php if ($entry instanceof LegacyWallEntryControlLink) : ?>
                <?= $entry->render() ?>
            <?php else: ?>
                <li>
                    <?= $entry->render() ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</li>
<?= Html::endTag('ul') ?>
