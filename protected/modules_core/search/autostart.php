<?php

Yii::app()->moduleManager->register(array(
    'isCoreModule' => true,
    'id' => 'search',
    'class' => 'application.modules_core.search.SearchModule',
    'import' => array(
        'application.modules_core.search.*',
        'application.modules_core.search.interfaces.*',
        'application.modules_core.search.engine.*',
        'application.modules_core.search.libs.*',
    ),

    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('SpaceModule', 'onUserDelete')),
        array('class' => 'HSearch', 'event' => 'onRebuild', 'callback' => array('SpaceModule', 'onSearchRebuild')),
        array('class' => 'TopMenuRightStackWidget', 'event' => 'onInit', 'callback' => array('SearchModuleEvents', 'onTopMenuRightInit')),
    ),
));
?>