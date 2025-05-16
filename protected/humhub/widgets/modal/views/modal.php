<?php

use humhub\components\View;
use humhub\widgets\LoaderWidget;
use humhub\widgets\modal\Modal;

/**
 * @var $this View
 * @var $title string
 * @var $body string
 * @var $footer string
 * @var $size string
 * @var $closable boolean
 * @var $backdrop boolean
 * @var $keyboard boolean
 * @var $show boolean
 * @var $initialLoader boolean
 * @var $options array
 */
?>

<?php Modal::begin([
    'options' => $options,
    'title' => $title,
    'size' => $size,
    'closable' => $closable,
    'backdrop' => $backdrop,
    'keyboard' => $keyboard,
    'show' => $show,
    'footer' => $footer,
]); ?>
<?= $body ?>
<?= ($initialLoader ?? ($body === null) ? LoaderWidget::widget() : '') ?>
<?php Modal::end() ?>
