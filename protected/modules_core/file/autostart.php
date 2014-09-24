<?php

Yii::app()->moduleManager->register(array(
    'id' => 'file',
    'class' => 'application.modules_core.file.FileModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.file.*',
        'application.modules_core.file.models.*',
        'application.modules_core.file.libs.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'WallEntryAddonWidget', 'event' => 'onInit', 'callback' => array('FileModuleEvents', 'onWallEntryAddonInit')),
        array('class' => 'ZCronRunner', 'event' => 'onDailyRun', 'callback' => array('FileModuleEvents', 'onCronDailyRun')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('FileModuleEvents', 'onIntegrityCheck')),
        array('class' => 'HActiveRecord', 'event' => 'onBeforeDelete', 'callback' => array('FileModuleEvents', 'onBeforeHActiveRecordDelete')),
    ),
));
?>