<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\space\widgets\SpacesFilters;

/* @var $filter string */
/* @var $data array */
?>

<div class="<?= $data['wrapperClass'] ?>">
    <div class="<?= $data['titleClass'] ?>"><?= $data['title'] ?></div>
    <?= SpacesFilters::renderFilterInput($filter, $data) ?>
</div>