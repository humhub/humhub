<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification, 'iconClass'=> 'icon-ok-sign approval accepted')); ?>
<?php echo Yii::t('SpaceModule.notifications', '{userName} approved your membership in {spaceName}', array('{userName}' => '<strong>' . $creator->displayName . '</strong>', '{spaceName}' => '<strong>' . $targetObject->name . '</strong>')); ?> 
<?php $this->endContent(); ?>
