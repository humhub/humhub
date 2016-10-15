<?php

use yii\helpers\Html;

?>

<?php echo $content; ?>

<?php if (isset($space) && $space !== null): ?>(<?php echo strip_tags(Yii::t('NotificationModule.views_notificationLayoutMail', 'via')); ?> <?php echo Html::encode($space->name); ?>)

<?php endif; ?><?php echo strip_tags(Yii::t('NotificationModule.views_notificationLayoutMail', 'see online')); ?>: <?php echo urldecode($url); ?>


