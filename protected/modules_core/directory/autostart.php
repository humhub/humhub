<?php

Yii::app()->moduleManager->register(array(
    'id' => 'directory',
    'class' => 'application.modules_core.directory.DirectoryModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.directory.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'TopMenuWidget', 'event' => 'onInit', 'callback' => array('DirectoryModule', 'onTopMenuInit')),
    ),
));
?>