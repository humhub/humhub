<?php

use humhub\components\View;
use humhub\helpers\Html;

/* @var $this View */
/* @var $rootElement string */
/* @var $options array */
/* @var $bodyLayout $string */
?>

<?= Html::beginTag($rootElement, $options) ?>

<?= $bodyLayout ?>

<?= Html::endTag($rootElement) ?>
