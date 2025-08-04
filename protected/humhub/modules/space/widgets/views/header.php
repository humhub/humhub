<?php

use humhub\components\View;
use humhub\modules\content\widgets\ContainerProfileHeader;
use humhub\modules\space\models\Space;

/* @var $this View */
/* @var $space  Space */


?>

<?= ContainerProfileHeader::widget(['container' => $space]) ?>
