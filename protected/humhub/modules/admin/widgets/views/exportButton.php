<?php

/**
 * @var string $csv
 * @var string $xsls
 */

use humhub\widgets\Button;

?>
<div class="btn-group">
    <button type="button" class="btn btn-info">
        <i class="fa fa-download"></i> <?= Yii::t('base', 'Export') ?>
    </button>
    <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
        <span class="caret"></span>
        <span class="sr-only">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu">
        <li><?= Button::asLink('csv', $csv)->pjax(false)
                ->icon('fa-file-code-o')->sm() ?></li>
        <li><?= Button::asLink('xlsx', $xsls)->pjax(false)
                ->icon('fa-file-excel-o')->sm() ?></li>
    </ul>
</div>