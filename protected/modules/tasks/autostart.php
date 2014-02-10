<?php

Yii::app()->moduleManager->register(array(
    'id' => 'tasks',
    'class' => 'application.modules.tasks.TasksModule',
    'title' => Yii::t('TasksModule.base', 'Tasks'),
    'description' => Yii::t('TasksModule.base', 'Adds a taskmanager to your spaces. With this module you can create and assign tasks to users in spaces.'),
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
    'spaceModules' => array(
        'tasks' => array(
            'title' => Yii::t('TasksModule.base', 'Tasks'),
            'description' => Yii::t('TasksModule.base', 'Adds a taskmanager to your spaces. With this module you can create and assign tasks to users in spaces.'),
        ),
    ),
    'contentModels' => array('Task'),
));
?>