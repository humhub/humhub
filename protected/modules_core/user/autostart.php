<?php

Yii::app()->moduleManager->register(array(
    'id' => 'user',
    'class' => 'application.modules_core.user.UserModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.user.components.*',
        'application.modules_core.user.models.*',
        'application.modules_core.user.widgets.*',
        'application.modules_core.user.notifications.*',
        'application.modules_core.user.forms.*',
        'application.modules_core.user.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'HSearch', 'event' => 'onRebuild', 'callback' => array('UserModuleEvents', 'onSearchRebuild')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('UserModuleEvents', 'onIntegrityCheck')),        
    ),
));
?>