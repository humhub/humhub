<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification)); ?>
<?php echo Yii::t('UserModule.views_notifications_Mentioned', '{userName} mentioned you in {contentTitle}.', array(
    '{userName}' => '<strong>' . $creator->displayName . '</strong>',
    '{contentTitle}' => NotificationModule::formatOutput($targetObject->getContentTitle())
)); ?>
<?php $this->endContent(); ?>