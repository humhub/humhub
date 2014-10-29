<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification)); ?>

<?php echo Yii::t('LikeModule.views_notifications_newLike', "%displayName% likes %contentTitle%.", array(
    '%displayName%' => '<strong>' . $creator->displayName . '</strong>',
    '%contentTitle%' => NotificationModule::formatOutput($targetObject->getContentTitle())
));
?>
<?php $this->endContent(); ?>