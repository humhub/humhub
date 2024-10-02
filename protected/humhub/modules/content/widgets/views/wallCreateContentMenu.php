<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\Html;
use humhub\modules\content\widgets\WallCreateContentMenu;
use humhub\modules\ui\menu\MenuEntry;

/* @var $menu WallCreateContentMenu */
/* @var $entries MenuEntry[] */
/* @var $options array */
?>
<?= Html::beginTag('div', $options) ?>
<ul class="nav nav-tabs">
    <?php foreach ($entries as $e => $entry) : ?>
        <?php $entry->setIsActive($e === 0) ?>
        <li class="nav-item<?= $entry->getIsActive() ? ' active' : '' ?>">
            <?= $entry->render(['class' => 'nav-link']) ?>
        </li>
        <?php if ($e == $menu->visibleEntriesNum - 1 && count($entries) > $menu->visibleEntriesNum) : ?>
            <li class="nav-item content-create-menu-more">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false"></a>
                <ul class="dropdown-menu float-end">
                    <?php foreach ($entries as $e => $entry) : ?>
                        <?php if ($e < $menu->visibleEntriesNum) {
                            continue;
                        } ?>
                        <li>
                            <?= $entry->render(['class' => 'dropdown-item']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php break; endif; ?>
    <?php endforeach; ?>
</ul>
<?= Html::endTag('div') ?>
