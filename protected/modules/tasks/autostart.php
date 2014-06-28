<?php

Yii::app()->moduleManager->register(array(
    'id' => 'tasks',
    'class' => 'application.modules.tasks.TasksModule',
    'import' => array(
        'application.modules.tasks.*',
        'application.modules.tasks.models.*',
        'application.modules.tasks.notifications.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'SpaceMenuWidget', 'event' => 'onInit', 'callback' => array('TasksModule', 'onSpaceMenuInit')),
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('TasksModule', 'onUserDelete')),
        array('class' => 'Space', 'event' => 'onBeforeDelete', 'callback' => array('TasksModule', 'onSpaceDelete')),
        array('class' => 'Space', 'event' => 'onUninstallModule', 'callback' => array('TasksModule', 'onSpaceUninstallModule')),
        array('class' => 'ModuleManager', 'event' => 'onDisable', 'callback' => array('TasksModule', 'onDisableModule')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('TasksModule', 'onIntegrityCheck')),
    ),
));
?>