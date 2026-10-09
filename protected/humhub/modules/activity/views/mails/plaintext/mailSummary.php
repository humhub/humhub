<?php

use yii\helpers\Url;

/* @var $activitiesPlaintext string */
/* @var $unseenNotificationCount int */

$unseenNotificationCount ??= 0;

?>
<?php if ($unseenNotificationCount > 0): ?>
<?= Yii::t(
    'NotificationModule.base',
    '{count,plural,=1{You have # unread notification} other{You have # unread notifications}}',
    ['count' => $unseenNotificationCount],
) ?>: <?= Url::to(['/notification/overview'], true) ?>


<?php endif ?>
<?= strip_tags(Yii::t('base', '<strong>Latest</strong> updates')) ?>
<?= $activitiesPlaintext ?>
