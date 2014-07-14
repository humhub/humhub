<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification)); ?>
<?php echo Yii::t('SpaceModule.notifications', '<strong>{userName}</strong> notified you about a new {contentTitle}.', array(
    '{userName}' => $creator->displayName,
    '{contentTitle}' => $targetObject->getContentTitle())); ?>
<?php $this->endContent(); ?>