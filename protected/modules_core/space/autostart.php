<?php

Yii::app()->moduleManager->register(array(
    'id' => 'space',
    'class' => 'application.modules_core.space.SpaceModule',
    'import' => array(
        'application.modules_core.space.behaviors.*',
        'application.modules_core.space.widgets.*',
        'application.modules_core.space.models.*',
        'application.modules_core.space.notifications.*',
        'application.modules_core.space.*',
    ),
    'isCoreModule' => true,

    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('SpaceModule', 'onUserDelete')),
        array('class' => 'HSearch', 'event' => 'onRebuild', 'callback' => array('SpaceModule', 'onSearchRebuild')),
    ),
));
?>