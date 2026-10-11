<?php

use humhub\modules\notification\components\NotificationPrivacy;
use yii\helpers\Url;

/* @var $activitiesPlaintext string the rendered activities; empty under NotificationPrivacy::Minimal */
/* @var $unseenNotificationCount int */
/* @var $activityCount int */
/* @var $privacy NotificationPrivacy */

$unseenNotificationCount ??= 0;
$activityCount ??= 0;
$privacy ??= NotificationPrivacy::Full;

?>
<?php if ($unseenNotificationCount > 0): ?>
<?= Yii::t(
    'NotificationModule.base',
    '{count,plural,=1{You have # unread notification} other{You have # unread notifications}}',
    ['count' => $unseenNotificationCount],
) ?>: <?= Url::to(['/notification/overview'], true) ?>


<?php endif ?>
<?= strip_tags(Yii::t('base', '<strong>Latest</strong> updates')) ?>
<?php if ($privacy === NotificationPrivacy::Minimal): ?>

<?= Yii::t(
    'ActivityModule.base',
    '{count,plural,=1{# new activity} other{# new activities}}',
    ['count' => $activityCount],
) ?>: <?= Url::to(['/dashboard/dashboard'], true) ?>

<?php endif ?>
<?= $activitiesPlaintext ?>
