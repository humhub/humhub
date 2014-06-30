<?php

Yii::app()->moduleManager->register(array(
    'id' => 'polls',
    'class' => 'application.modules.polls.PollsModule',
    'import' => array(
        'application.modules.polls.models.*',
        'application.modules.polls.behaviors.*',
        'application.modules.polls.notifications.*',
        'application.modules.polls.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('PollsModule', 'onUserDelete')),
        array('class' => 'SpaceMenuWidget', 'event' => 'onInit', 'callback' => array('PollsModule', 'onSpaceMenuInit')),
    ),
));
?>