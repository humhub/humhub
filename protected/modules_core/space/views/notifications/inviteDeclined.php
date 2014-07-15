<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification, 'iconClass' => 'fa fa-minus-circle approval declined')); ?>
<?php echo Yii::t('SpaceModule.views_notifications_inviteDeclined', '{userName} declined your invite for the space {spaceName}', array(
    '{userName}' => '<strong>' . $creator->displayName . '</strong>',
    '{spaceName}' => '<strong>' . $targetObject->name . '</strong>'
)); ?>
<?php $this->endContent(); ?>