<?php

use humhub\modules\content\widgets\ContainerProfileHeader;
use humhub\modules\space\models\Space;
use humhub\modules\ui\view\components\View;

/* @var $this View */
/* @var $space  Space */


?>

<?= ContainerProfileHeader::widget(['container' => $space]) ?>
