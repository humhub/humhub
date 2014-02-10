<?php

Yii::app()->moduleManager->register(array(
    'id' => 'wall',
    'title' => Yii::t('WallModule.base', 'Wall'),
    'description' => Yii::t('WallModule.base', 'Adds the wall/streaming core module.'),
    'class' => 'application.modules_core.wall.WallModule',
    'import' => array(
        'application.modules_core.wall.*',
        'application.modules_core.wall.models.*',
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