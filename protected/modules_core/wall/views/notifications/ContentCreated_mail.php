<?php $this->beginContent('application.modules_core.notification.views.notificationLayoutMail', array('notification' => $notification, 'showSpace' => true)); ?>
<?php echo Yii::t('SpaceModule.notifications', '{userName} notified you about a new {contentTitle}.', array(
    '{userName}' => '<strong>' . $creator->displayName . '</strong>',
    '{contentTitle}' => $targetObject->getContentTitle()
)); ?>
<?php $this->endContent(); ?>