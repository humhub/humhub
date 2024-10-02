<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\menu\MenuEntry;
use humhub\widgets\bootstrap\Button;

/* @var MenuEntry[] $entries */
?>
<?= Button::secondary(Icon::get('cog'))
    ->options(['data-bs-toggle' => 'dropdown'])
    ->sm()
    ->cssClass('dropdown-toggle')
    ->loader(false) ?>
<ul class="dropdown-menu float-end">
    <?php foreach ($entries as $entry) : ?>
        <li>
            <?= $entry->render(['class' => 'dropdown-item']) ?>
        </li>
    <?php endforeach; ?>
</ul>
