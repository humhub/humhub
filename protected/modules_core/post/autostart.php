<?php

Yii::app()->moduleManager->register(array(
    'id' => 'post',
    'class' => 'application.modules_core.post.PostModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.post.*',
        'application.modules_core.post.models.*',
        'application.modules_core.post.notifications.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('PostModule', 'onIntegrityCheck')),
        array('class' => 'HSearch', 'event' => 'onRebuild', 'callback' => array('PostModule', 'onSearchRebuild')),
    ),
));
?>