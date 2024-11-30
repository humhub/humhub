<?php

use humhub\helpers\Html;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $rootElement string */
/* @var $options array */
/* @var $bodyLayout $string */
?>

<?= Html::beginTag($rootElement, $options) ?>

<?= $bodyLayout ?>

<?= Html::endTag($rootElement) ?>
