<?php

Yii::app()->moduleManager->register(array(
    'id' => 'post',
    'class' => 'application.modules_core.post.PostModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.post.*',
        'application.modules_core.post.models.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('PostModule', 'onIntegrityCheck')),
        array('class' => 'HSearch', 'event' => 'onRebuild', 'callback' => array('PostModule', 'onSearchRebuild')),
    ),
));
?>