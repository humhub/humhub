<?php

use humhub\modules\content\widgets\ContainerProfileHeader;

/* @var $this \humhub\components\View */
/* @var $user \humhub\modules\user\models\User */

?>

<?= ContainerProfileHeader::widget(['container' => $user]) ?>
