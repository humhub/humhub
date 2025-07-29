<?php

use humhub\components\View;
use humhub\modules\content\widgets\ContainerProfileHeader;

/* @var $this View */
/* @var $user \humhub\modules\user\models\User */

?>

<?= ContainerProfileHeader::widget(['container' => $user]) ?>
