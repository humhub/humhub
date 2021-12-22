<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\ui\icon\widgets\Icon;

/* @var string $class */
/* @var string $icon */
/* @var string $info */
/* @var string $link */
?>
<div class="row directory-filters-footer <?= $class ?>">
    <div class="col-xs-1 filter-footer-icon">
        <?= Icon::get($icon) ?>
    </div>
    <div class="col-md-7 col-xs-11">
        <strong><?= $info ?></strong>
    </div>
    <div class="col-md-4 col-xs-12 text-right">
        <?= $link ?>
    </div>
</div>