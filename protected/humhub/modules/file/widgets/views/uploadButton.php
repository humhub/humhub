<?php

use humhub\helpers\Html;
use humhub\modules\ui\icon\widgets\Icon;

/* @var $label string */
/* @var $input string */

?>

<?= Html::beginTag('span', $options) ?>
<?= Icon::get('cloud-upload') ?> <?= $label ?>
<?= $input ?>
<?= Html::endTag('span') ?>
