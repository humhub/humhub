<?php $this->beginContent('application.modules_core.notification.views.notificationLayoutMail', array('notification' => $notification)); ?>
<?php echo Yii::t('AdminModule.views_notifications_newUpdate', "There is a new HumHub Version (%version%) available.", array('%version%' => $notification->getLatestHumHubVersion())); ?>
<?php $this->endContent(); ?>