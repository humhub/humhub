<?php

/**
 * @var string $csv
 * @var string $xlsx
 */

use humhub\widgets\bootstrap\Link;

?>
<div class="btn-group btn-group-sm">
    <button type="button" class="btn btn-accent">
        <i class="fa fa-download"></i> <?= Yii::t('base', 'Export') ?>
    </button>
    <button type="button" class="btn btn-accent btn-icon-only dropdown-toggle" data-bs-toggle="dropdown">
        <span class="sr-only">Toggle Dropdown</span>
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
