<?php $this->beginContent('application.modules_core.notification.views.notificationLayoutMail', array('notification' => $notification, 'showSpace' => true)); ?>
<?php

echo Yii::t('UserModule.views_notifications_follow', '{userName} is now following you.', array(
    '{userName}' => '<strong>' . $creator->displayName . '</strong>',
));
?>
<?php $this->endContent(); ?>