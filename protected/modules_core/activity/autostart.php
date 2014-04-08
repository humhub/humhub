<?php

Yii::app()->moduleManager->register(array(
    'id' => 'activity',
    'title' => Yii::t('ActivityModule.base', 'Activities'),
    'description' => Yii::t('ActivityModule.base', 'Adds the activities core module.'),
    'class' => 'application.modules_core.activity.ActivityModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.activity.*',
        'application.modules_core.activity.models.*',
        'application.modules_core.activity.widgets.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onAfterDelete', 'callback' => array('ActivityModule', 'onUserDelete')),
        array('class' => 'Space', 'event' => 'onBeforeDelete', 'callback' => array('ActivityModule', 'onSpaceDelete')),
        array('class' => 'HActiveRecord', 'event' => 'onBeforeDelete', 'callback' => array('ActivityModule', 'onActiveRecordDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('ActivityModule', 'onIntegrityCheck')),
       ),
    'contentModels' => array('Activity'),
));
?>