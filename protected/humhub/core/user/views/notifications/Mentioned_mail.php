<?php $this->beginContent('application.modules_core.notification.views.notificationLayoutMail', array('notification' => $notification, 'showSpace' => true)); ?>
<?php

echo Yii::t('UserModule.views_notifications_Mentioned', '{userName} mentioned you in {contentTitle}.', array(
    '{userName}' => '<strong>' . CHtml::encode($creator->displayName) . '</strong>',
    '{contentTitle}' => NotificationModule::formatOutput($targetObject->getContentTitle())
));
?>
<?php $this->endContent(); ?>