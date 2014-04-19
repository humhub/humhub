<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification)); ?>

<strong><?php echo $creator->displayName; ?></strong>
<?php echo Yii::t('PollModule.base', 'created a new poll and assigned you.'); ?>

<?php $this->endContent(); ?>





