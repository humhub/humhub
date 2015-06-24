<?php

Yii::app()->moduleManager->register(array(
    'id' => 'wall',
    'class' => 'application.modules_core.wall.WallModule',
    'import' => array(
        'application.modules_core.wall.*',
        'application.modules_core.wall.models.*',
        'application.modules_core.wall.widgets.*',
        'application.modules_core.wall.notifications.*',
    ),
    'isCoreModule' => true,
    // Events to Catch 
    'events' => array(
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('WallModule', 'onIntegrityCheck')),
        array('class' => 'WallEntryControlsWidget', 'event' => 'onInit', 'callback' => array('WallModule', 'onWallEntryControlsInit')),
        array('class' => 'WallEntryAddonWidget', 'event' => 'onInit', 'callback' => array('WallModule', 'onWallEntryAddonInit')),
    ),
));
?>