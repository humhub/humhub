<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification)); ?>

<?php echo Yii::t('CommentModule.views_notifications_alsoCommented', "%displayName% also commented your %contentTitle%.", array(
    '%displayName%' => '<strong>' . $creator->displayName . '</strong>',
    '%contentTitle%' => $targetObject->getContentTitle()
));
?>
<em>"<?php echo NotificationModule::formatOutput($sourceObject->message); ?>"</em>

<?php $this->endContent(); ?>