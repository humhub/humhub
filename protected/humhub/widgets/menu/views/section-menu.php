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
use humhub\widgets\menu\SectionMenu;

/* @var $this View */
/* @var $menu SectionMenu */
/* @var $entries MenuEntry[] */
/* @var $options array */
?>
<?= Html::beginTag('nav', $options) ?>
    <?php if ($menu->hasTitle()) : ?>
        <h2 id="<?= $menu->getTitleId() ?>" class="section-menu-title"><?= Html::encode($menu->title) ?></h2>
    <?php endif; ?>
    <ul class="nav flex-lg-column nav-section">
        <?php foreach ($entries as $entry) : ?>
            <?php if ($entry instanceof MenuLink) : ?>
                <?php $entry->appendWaitingDot(); // something waits behind the section: a dot, the number for screen readers ?>
            <?php endif; ?>
            <li class="nav-item">
                <?php // the entry adds `active` and `aria-current` itself ?>
                <?= $entry->render(['class' => 'nav-link']) ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?= Html::endTag('nav') ?>
