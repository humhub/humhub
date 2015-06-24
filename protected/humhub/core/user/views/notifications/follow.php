<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification)); ?>
<?php echo Yii::t('UserModule.views_notifications_follow', '{userName} is now following you.', array(
    '{userName}' => '<strong>' . $creator->displayName . '</strong>',
)); ?>
<?php $this->endContent(); ?>