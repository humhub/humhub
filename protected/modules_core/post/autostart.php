<?php

Yii::app()->moduleManager->register(array(
    'id' => 'post',
    'title' => Yii::t('PostModule.base', 'Post'),
    'description' => Yii::t('PostModule.base', 'Basic subsystem for workspace/user post.'),
    'class' => 'application.modules_core.post.PostModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.post.*',
        'application.modules_core.post.models.*',
        'application.modules_core.post.notifications.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('PostModule', 'onUserDelete')),
        array('class' => 'Space', 'event' => 'onBeforeDelete', 'callback' => array('PostModule', 'onSpaceDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('PostModule', 'onIntegrityCheck')),
        array('class' => 'HSearch', 'event' => 'onRebuild', 'callback' => array('PostModule', 'onSearchRebuild')),
    ),
    'contentModels' => array('Activity'),
));
?>