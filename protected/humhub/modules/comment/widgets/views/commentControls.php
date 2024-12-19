<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\ui\menu\MenuEntry;

/* @var MenuEntry[] $entries */
/* @var array $options */
?>

<div class="comment-entry-loader float-end"></div>
<?= Html::beginTag('ul', $options) ?>
<li class="dropdown ">
    <?= Html::a('', '#', [
        'class' => 'dropdown-toggle',
        'data-bs-toggle' => 'dropdown',
        'aria-label' => Yii::t('base', 'Toggle comment menu'),
        'aria-haspopup' => 'true',
        'aria-expanded' => 'false',
        'role' => 'button',
    ]) ?>

    <ul class="dropdown-menu float-end">
        <?php foreach ($entries as $entry) : ?>
            <li>
                <?= $entry->render(['class' => 'dropdown-item']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</li>
<?= Html::endTag('ul') ?>
