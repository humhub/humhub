<?php

use humhub\modules\content\widgets\ContainerProfileHeader;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $user \humhub\modules\user\models\User */

?>

<?= ContainerProfileHeader::widget(['container' => $user]) ?>
