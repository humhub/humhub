<?php

use humhub\libs\Html;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $rootElement string */
/* @var $options array */
/* @var $contentLayout $string */
?>

<?= Html::beginTag($rootElement,  $options)?>

    <?= $contentLayout ?>

<?= Html::endTag($rootElement) ?>

