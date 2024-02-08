<?php

use humhub\modules\content\widgets\ContainerProfileHeader;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $user \humhub\modules\user\models\User */

?>

<?= ContainerProfileHeader::widget(['container' => $user]) ?>
