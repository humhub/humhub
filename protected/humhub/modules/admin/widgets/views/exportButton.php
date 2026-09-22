<?php

/**
 * @var string $csv
 * @var string $xlsx
 */

use humhub\widgets\bootstrap\Link;
use humhub\widgets\Icon;

?>
<div class="btn-group btn-group-sm">
    <button type="button" class="btn btn-accent">
        <?= Icon::get('download') ?> <?= Yii::t('base', 'Export') ?>
    </button>
    <button type="button" class="btn btn-accent btn-icon-only dropdown-toggle" data-bs-toggle="dropdown">
        <span class="visually-hidden">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu">
        <li><?= Link::to('csv', $csv, false)
                ->cssClass('dropdown-item')
                ->icon('file-code-o')->sm() ?></li>
        <li><?= Link::to('xlsx', $xlsx, false)
                ->cssClass('dropdown-item')
                ->icon('file-excel-o')->sm() ?></li>
    </ul>
</div>
