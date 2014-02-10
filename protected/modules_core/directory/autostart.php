<?php

Yii::app()->moduleManager->register(array(
    'id' => 'directory',
    'title' => Yii::t('DirectoryModule.base', 'Directory'),
    'description' => Yii::t('DirectoryModule.base', 'Adds an directory to the main navigation.'),
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