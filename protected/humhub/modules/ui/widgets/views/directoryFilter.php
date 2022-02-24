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
    <?php if(isset($data['title'])) : ?>
        <div class="<?= $data['titleClass'] ?>"><?= $data['title'] ?></div>
    <?php endif; ?>
    <?= $directoryFilters->renderFilterInput($filter, $data) ?>
</div>