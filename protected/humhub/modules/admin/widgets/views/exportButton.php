<?php

/**
 * @var string $csv
 * @var string $xlsx
 */

use humhub\widgets\bootstrap\Button;

?>
<div class="btn-group">
    <button type="button" class="btn btn-sm btn-info">
        <i class="fa fa-download"></i> <?= Yii::t('base', 'Export') ?>
    </button>
    <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-bs-toggle="dropdown">
        <span class="sr-only">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu">
        <li><?= Button::asLink('csv', $csv)->pjax(false)
                ->icon('fa-file-code-o')->sm() ?></li>
        <li><?= Button::asLink('xlsx', $xlsx)->pjax(false)
                ->icon('fa-file-excel-o')->sm() ?></li>
    </ul>
</div>
