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
        array('class' => 'TopMenuRightStackWidget', 'event' => 'onInit', 'callback' => array('SearchModuleEvents', 'onTopMenuRightInit')),
        array('class' => 'Comment', 'event' => 'onAfterSave', 'callback' => array('SearchModuleEvents', 'onAfterSaveComment')),
    ),
));
?>