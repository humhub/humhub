<?php

Yii::app()->moduleManager->register(array(
    'id' => 'notification',
    'class' => 'application.modules_core.notification.NotificationModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.notification.*',
        'application.modules_core.notification.models.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('NotificationModule', 'onUserDelete')),
        array('class' => 'Space', 'event' => 'onBeforeDelete', 'callback' => array('NotificationModule', 'onSpaceDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('NotificationModule', 'onIntegrityCheck')),
        array('class' => 'ZCronRunner', 'event' => 'onDailyRun', 'callback' => array('NotificationModule', 'onCronDailyRun')),
    ),
));
?>