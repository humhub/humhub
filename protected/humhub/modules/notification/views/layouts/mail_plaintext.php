<?php

use yii\helpers\Html;
?>

<?= $content; ?>

<?php if (isset($space) && $space !== null): ?>(<?= strip_tags(Yii::t('NotificationModule.views_notificationLayoutMail', 'via')); ?> <?= Html::encode($space->name); ?>)

<?php endif; ?><?= strip_tags(Yii::t('NotificationModule.views_notificationLayoutMail', 'see online')); ?>: <?= urldecode($url); ?>