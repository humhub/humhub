<?php

Yii::app()->moduleManager->register(array(
    'id' => 'activity',
    'class' => 'application.modules_core.activity.ActivityModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.activity.*',
        'application.modules_core.activity.models.*',
        'application.modules_core.activity.widgets.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onAfterDelete', 'callback' => array('ActivityModuleEvents', 'onUserDelete')),
        array('class' => 'Space', 'event' => 'onBeforeDelete', 'callback' => array('ActivityModuleEvents', 'onSpaceDelete')),
        array('class' => 'HActiveRecord', 'event' => 'onBeforeDelete', 'callback' => array('ActivityModuleEvents', 'onActiveRecordDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('ActivityModuleEvents', 'onIntegrityCheck')),
    ),
));
?>