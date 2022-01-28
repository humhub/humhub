<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\ui\widgets\DirectoryFilters;

/* @var $directoryFilters DirectoryFilters */
/* @var $filter string */
/* @var $data array */
?>

<div class="<?= $data['wrapperClass'] ?>">
    <div class="<?= $data['titleClass'] ?>"><?= $data['title'] ?></div>
    <?= $directoryFilters->renderFilterInput($filter, $data) ?>
</div>