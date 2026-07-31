<?php

use humhub\helpers\Html;
use humhub\modules\ui\icon\widgets\Icon;

/* @var $label string */
/* @var $input string */
/* @var $options array */
?>
<?= Html::tag('button', Icon::get('cloud-upload') . ' ' . Html::encode($label), $options) ?>
<?= $input ?>
