<?php $this->beginContent('application.modules_core.notification.views.notificationLayoutMail', array('notification' => $notification)); ?>
<?php echo Yii::t('SpaceModule.notifications', '<strong>{userName}</strong> declined your membership request for the space <strong>{spaceName}</strong>', array(
    '{userName}' => $creator->displayName,
    '{spaceName}' => $targetObject->name)); ?>
<?php $this->endContent(); ?>