<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification)); ?>

<?php echo Yii::t('CommentModule.views_notifications_newCommented', "%displayName% commented %contentTitle%.", array(
    '%displayName%' => '<strong>' . CHtml::encode($creator->displayName) . '</strong>',
    '%contentTitle%' => NotificationModule::formatOutput($targetObject->getContentTitle())
));
?>
    <em>"<?php echo NotificationModule::formatOutput($sourceObject->message); ?>"</em>

<?php $this->endContent(); ?>