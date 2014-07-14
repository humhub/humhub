<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification)); ?>
<?php echo Yii::t('SpaceModule.notifications', '<strong>{userName}</strong> invited you to the space <strong>{spaceName}</strong>', array(
    '{userName}' => $creator->displayName,
    '{spaceName}' => $targetObject->name)); ?>
<?php $this->endContent(); ?>