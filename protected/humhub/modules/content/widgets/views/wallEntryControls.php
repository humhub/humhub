<?php

use humhub\libs\Html;
use humhub\modules\content\widgets\LegacyWallEntryControlLink;

/* @var $this \humhub\components\View */
/* @var $menu \humhub\modules\ui\menu\widgets\DropdownMenu */
/* @var $entries \humhub\modules\ui\menu\MenuEntry[] */
/* @var $options [] */
?>

<?= Html::beginTag('ul', $options)?>
    <li class="dropdown ">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"
           aria-label="<?= Yii::t('base', 'Toggle stream entry menu'); ?>" aria-haspopup="true">
            <i class="fa fa-angle-down"></i>
        </a>

        <ul class="dropdown-menu pull-right">
            <?php foreach ($entries as $entry) : ?>
                <?php if($entry instanceof LegacyWallEntryControlLink) : ?>
                    <?= $entry->render() ?>
                <?php else: ?>
                    <li>
                        <?= $entry->render() ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </li>
<?= Html::endTag('ul')?>
