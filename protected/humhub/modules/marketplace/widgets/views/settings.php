<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\ui\menu\MenuEntry;
use humhub\widgets\bootstrap\Button;

/* @var MenuEntry[] $entries */
/* @var array $options */
?>
<?= Html::beginTag('ul', $options) ?>
<?= Html::beginTag('li', ['class' => 'dropdown']) ?>
<?= Button::asLink()
    ->icon('cog')
    ->cssClass('dropdown-toggle')
    ->tooltip(Yii::t('MarketplaceModule.base', 'Settings'))
    ->options(['data-bs-toggle' => 'dropdown']) ?>

<ul class="dropdown-menu dropdown-menu-end">
    <?php foreach ($entries as $entry) : ?>
        <li>
            <?= $entry->render(['class' => 'dropdown-item']) ?>
        </li>
    <?php endforeach; ?>
</ul>
<?= Html::endTag('li') ?>
<?= Html::endTag('ul') ?>
