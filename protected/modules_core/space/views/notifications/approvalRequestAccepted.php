<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification, 'iconClass'=> 'fa fa-check-circle approval accepted')); ?>
<?php echo Yii::t('SpaceModule.notifications', '<strong>{userName}</strong> approved your membership for the space <strong>{spaceName}</strong>', array(
    '{userName}' => $creator->displayName,
    '{spaceName}' => $targetObject->name)); ?>
<?php $this->endContent(); ?>
