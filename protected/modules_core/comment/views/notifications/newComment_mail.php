<?php $this->beginContent('application.modules_core.notification.views.notificationLayoutMail', array('notification' => $notification)); ?>

<?php echo Yii::t('CommentModule.notification', "%displayName% commented your %contentTitle%.", array(
   '%displayName%' => '', // Above in e-mail main layout, kept for same translation message as web notification.
   '%contentTitle%' => $targetObject->getContentTitle()
)); ?>

<br />

<em>"<?php echo NotificationModule::formatOutput($sourceObject->message); ?>"</em>

<?php $this->endContent(); ?>