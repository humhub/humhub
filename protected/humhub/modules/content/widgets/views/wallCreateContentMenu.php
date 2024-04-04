<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\libs\Html;
use humhub\modules\content\widgets\WallCreateContentMenu;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\menu\MenuEntry;

/* @var $menu WallCreateContentMenu */
/* @var $entries MenuEntry[] */
/* @var $options array */
?>
<?= Html::beginTag('div', $options) ?>
<ul class="nav nav-tabs">
    <?php foreach ($entries as $e => $entry) : ?>
        <?php $entry->setIsActive($e === 0) ?>
        <li<?= $entry->getIsActive() ? ' class="active"' : '' ?>>
            <?= $entry->render() ?>
        </li>
        <?php if ($e == $menu->visibleEntriesNum - 1 && count($entries) > $menu->visibleEntriesNum) : ?>
            <li class="content-create-menu-more">
                <?= Icon::get('caret-down', ['htmlOptions' => ['data-toggle' => 'dropdown']]) ?>
                <ul class="dropdown-menu pull-right">
                    <?php foreach ($entries as $e => $entry) : ?>
                        <?php if ($e < $menu->visibleEntriesNum) {
                            continue;
                        } ?>
                        <li>
                            <?= $entry->render() ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php break; endif; ?>
    <?php endforeach; ?>
</ul>
<?= Html::endTag('div') ?>
