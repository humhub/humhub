<?php $this->beginContent('application.modules_core.notification.views.notificationLayoutMail', array('notification' => $notification, 'showSpace' => true)); ?>

<?php echo Yii::t('LikeModule.views_notifications_alsoLike', "%displayName% also likes the %contentTitle%.", array(
    '%displayName%' => '<strong>' . $creator->displayName . '</strong>',
    '%contentTitle%' => $targetObject->getContentTitle()
));
?>
<?php $this->endContent(); ?>