<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification)); ?>
<?php echo Yii::t('WallModule.views_notifications_ContentCreated', '{userName} created a new {contentTitle}.', array(
    '{userName}' => '<strong>' . CHtml::encode($creator->displayName) . '</strong>',
    '{contentTitle}' => NotificationModule::formatOutput($targetObject->getContentTitle())
)); ?>
<?php $this->endContent(); ?>