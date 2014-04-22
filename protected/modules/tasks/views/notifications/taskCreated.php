<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification)); ?>

<strong><?php echo $creator->displayName; ?></strong>
<?php echo Yii::t('TasksModule.base', 'created the task '); ?>
<strong><?php echo $targetObject->getContentTitle(); ?></strong>.

<?php $this->endContent(); ?>





