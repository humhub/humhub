<?php $this->beginContent('application.modules_core.notification.views.notificationLayoutMail', array('notification' => $notification, 'showSpace' => true)); ?>
<?php echo Yii::t('SpaceModule.notifications', '<strong>{userName}</strong> notified you about a new {contentTitle}.', array(
    '{userName}' => $creator->displayName,
    '{contentTitle}' => $targetObject->getContentTitle())); ?>
<?php $this->endContent(); ?>