<?php

use humhub\widgets\bootstrap\Html;

/* @var $label string */
/* @var $input string */

?>

<?= Html::beginTag('span', $options) ?>
<i class="fa fa-cloud-upload" aria-hidden="true"></i> <?= $label ?>
<?= $input ?>
<?= Html::endTag('span') ?>
