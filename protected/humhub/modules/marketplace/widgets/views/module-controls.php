<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\ui\menu\MenuEntry;

/* @var MenuEntry[] $entries */
/* @var array $options */
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

    <ul class="dropdown-menu">
        <?php foreach ($entries as $entry) : ?>
            <li>
                <?= $entry->render(['class' => 'dropdown-item']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</li>
<?= Html::endTag('ul') ?>
