<?php

use humhub\modules\ui\view\components\View;
use humhub\widgets\LoaderWidget;
use humhub\widgets\modal\Modal;

/**
 * @var $this View
 * @var $title string
 * @var $body string
 * @var $footer string
 * @var $size string
 * @var $initialLoader boolean
 * @var $options array
 */
?>

<?php Modal::begin([
    'options' => $options,
    'title' => $title,
    'size' => $size,
    'footer' => $footer,
]); ?>
<?= $body ?>
<?= ($initialLoader ?? ($body === null) ? LoaderWidget::widget() : '') ?>
<?php Modal::end() ?>
