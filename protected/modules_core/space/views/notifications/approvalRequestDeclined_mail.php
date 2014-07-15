<?php $this->beginContent('application.modules_core.notification.views.notificationLayoutMail', array('notification' => $notification)); ?>
<?php echo Yii::t('SpaceModule.views_notifications_approvalRequestDeclined', '{userName} declined your membership request for the space {spaceName}', array(
    '{userName}' => '<strong>' . $creator->displayName . '</strong>',
    '{spaceName}' => '<strong>' . $targetObject->name . '</strong>'
)); ?>
<?php $this->endContent(); ?>