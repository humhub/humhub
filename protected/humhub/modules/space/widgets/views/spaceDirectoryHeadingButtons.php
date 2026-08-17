<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\space\widgets\SpaceDirectoryHeadingButtons;
use humhub\modules\ui\menu\MenuEntry;

/* @var $menu SpaceDirectoryHeadingButtons */
/* @var $entries MenuEntry[] */
?>
<?php foreach ($entries as $entry) : ?>
    <?= $entry->render(['class' => 'btn btn-accent btn-sm pull-right ms-1']) ?>
<?php endforeach ?>
