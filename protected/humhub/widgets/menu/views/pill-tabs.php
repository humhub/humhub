<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\widgets\menu\MenuEntry;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\PillTabs;

/* @var $this View */
/* @var $menu PillTabs */
/* @var $entries MenuEntry[] */
/* @var $options array */
?>
<?= Html::beginTag('nav', $options) ?>
    <?= Html::beginTag('ul', ['class' => ['nav', 'nav-pills', $menu->variant === PillTabs::VARIANT_SECONDARY ? 'nav-pills-secondary' : null]]) ?>
        <?php foreach ($entries as $entry) : ?>
            <?php if ($entry instanceof MenuLink && $entry->getCount() > 0) : ?>
                <?php // Something waits behind the tab: a dot, the number for screen readers
                $link = $entry->getLink();
                $entry->setLabel(($link->encodeLabel ? Html::encode($link->label) : $link->label)
                    . Html::tag('span', Html::tag('span', ' ' . Yii::t('base', '{count} waiting', ['count' => $entry->getCount()]), ['class' => 'visually-hidden']), ['class' => 'badge-dot badge-dot-waiting']));
                $entry->setEncodeLabel(false);
                ?>
            <?php endif; ?>
            <li class="nav-item">
                <?php // the entry adds `active` itself ?>
                <?= $entry->render(array_filter([
                    'class' => 'nav-link',
                    'aria-current' => $entry->getIsActive() ? 'page' : null,
                ])) ?>
            </li>
        <?php endforeach; ?>
    <?= Html::endTag('ul') ?>
<?= Html::endTag('nav') ?>
