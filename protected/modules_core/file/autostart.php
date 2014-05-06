<?php

Yii::app()->moduleManager->register(array(
    'id' => 'file',
    'title' => Yii::t('FileModule.base', 'File'),
    'description' => Yii::t('FileModule.base', 'Files core module.'),
    'class' => 'application.modules_core.file.FileModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.file.*',
        'application.modules_core.file.models.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'WallEntryAddonWidget', 'event' => 'onInit', 'callback' => array('FileModule', 'onWallEntryAddonInit')),
        array('class' => 'ZCronRunner', 'event' => 'onDailyRun', 'callback' => array('FileModule', 'onCronDailyRun')),
    ),
));
?>