<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification)); ?>

<?php echo Yii::t('LikeModule.notification', "%displayName% also likes the %contentTitle%.", array(
    '%displayName%' => '<strong>' . $creator->displayName . '</strong>',
    '%contentTitle%' => $targetObject->getContentTitle()
));
?>
<?php $this->endContent(); ?>