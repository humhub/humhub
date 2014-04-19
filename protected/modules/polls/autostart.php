<?php

Yii::app()->moduleManager->register(array(
    'id' => 'polls',
    'class' => 'application.modules.polls.PollsModule',
    'title' => Yii::t('PollsModule.base', 'Polls'),
    'description' => Yii::t('PollsModule.base', 'Adds polling features to spaces.'),
    'import' => array(
        'application.modules.polls.models.*',
        'application.modules.polls.behaviors.*',
        'application.modules.polls.notifications.*',
        'application.modules.polls.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('PollsModule', 'onUserDelete')),
        array('class' => 'Space', 'event' => 'onBeforeDelete', 'callback' => array('PollsModule', 'onSpaceDelete')),
        array('class' => 'Space', 'event' => 'onUninstallModule', 'callback' => array('PollsModule', 'onSpaceUninstallModule')),
        array('class' => 'SpaceMenuWidget', 'event' => 'onInit', 'callback' => array('PollsModule', 'onSpaceMenuInit')),
        array('class' => 'ModuleManager', 'event' => 'onDisable', 'callback' => array('PollsModule', 'onDisableModule')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('PollsModule', 'onIntegrityCheck')),
    ),
    'spaceModules' => array(
        'polls' => array(
            'title' => Yii::t('PollsModule.base', 'Polls'),
            'description' => Yii::t('PollsModule.base', 'Adds polling features to your space.'),
        ),
    ),
    'contentModels' => array('Poll'),
));
?>