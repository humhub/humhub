<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\ui\menu\MenuEntry;
use humhub\widgets\bootstrap\Button;

/* @var MenuEntry[] $entries */
/* @var array $options */
?>

<?= Html::beginTag('ul', $options) ?>
<li class="nav-item dropdown">
    <?= Button::light()
            ->icon('ellipsis-h')
            ->options([
                'data-bs-toggle' => 'dropdown',
                'aria-label' => Yii::t('MarketplaceModule.base', 'Module options'),
                'aria-haspopup' => 'true',
                'aria-expanded' => 'false',
            ])
            ->sm()
            ->cssClass('nav-link dropdown-toggle')
            ->loader(false) ?>
    <ul class="dropdown-menu dropdown-menu-end">
        <?php foreach ($entries as $entry) : ?>
            <li>
                <?= $entry->render(['class' => 'dropdown-item']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</li>
<?= Html::endTag('ul') ?>
