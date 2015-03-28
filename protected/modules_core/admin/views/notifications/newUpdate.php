<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification)); ?>
<strong><?php echo Yii::t('AdminModule.views_notifications_newUpdate', "There is a new HumHub Version (%version%) available.", array('%version%' => $notification->getLatestHumHubVersion())); ?></strong>
<?php $this->endContent(); ?>