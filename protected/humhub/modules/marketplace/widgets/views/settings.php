<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\ui\menu\MenuEntry;
use humhub\widgets\Button;

/* @var MenuEntry[] $entries */
/* @var array $options */
?>
<?= Html::beginTag('ul', $options) ?>
<?= Html::beginTag('li', ['class' => 'dropdown']) ?>
<?= Button::asLink()
    ->icon('cog')
    ->cssClass('dropdown-toggle')
    ->tooltip(Yii::t('MarketplaceModule.base', 'Settings'))
    ->options(['data-toggle' => 'dropdown']) ?>

<ul class="dropdown-menu pull-right">
    <?php foreach ($entries as $entry) : ?>
        <li>
            <?= $entry->render() ?>
        </li>
    <?php endforeach; ?>
</ul>
<?= Html::endTag('li') ?>
<?= Html::endTag('ul') ?>
