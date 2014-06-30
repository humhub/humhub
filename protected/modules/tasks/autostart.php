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
    ),
));
?>