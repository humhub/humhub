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
    <div class="col-lg-8 col-12">
        <?= Icon::get($icon, ['htmlOptions' => ['class' => 'filter-footer-icon']]) ?>
        <strong><?= $info ?></strong>
    </div>
    <div class="col-lg-4 col-12 text-end">
        <?= $link ?>
    </div>
</div>
