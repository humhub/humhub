<?php

Yii::app()->moduleManager->register(array(
    'id' => 'mail',
    'class' => 'application.modules.mail.MailModule',
    'import' => array(
        'application.modules.mail.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('MailModule', 'onUserDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('MailModule', 'onIntegrityCheck')),
        array('class' => 'TopMenuWidget', 'event' => 'onInit', 'callback' => array('MailModule', 'onTopMenuInit')),
        array('class' => 'NotificationAddonWidget', 'event' => 'onInit', 'callback' => array('MailModule', 'onNotificationAddonInit')),
    ),
));
?>