<?php

Yii::app()->moduleManager->register(array(
    'id' => 'like',
    'class' => 'application.modules_core.like.LikeModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.like.*',
        'application.modules_core.like.models.*',
        'application.modules_core.like.notifications.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('LikeModule', 'onUserDelete')),
        array('class' => 'HActiveRecordContent', 'event' => 'onBeforeDelete', 'callback' => array('LikeModule', 'onContentDelete')),
        array('class' => 'HActiveRecordContentAddon', 'event' => 'onBeforeDelete', 'callback' => array('LikeModule', 'onContentAddonDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('LikeModule', 'onIntegrityCheck')),
        array('class' => 'WallEntryLinksWidget', 'event' => 'onInit', 'callback' => array('LikeModule', 'onWallEntryLinksInit')),
        array('class' => 'WallEntryAddonWidget', 'event' => 'onInit', 'callback' => array('LikeModule', 'onWallEntryAddonInit')),
    ),
));
?>