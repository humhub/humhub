<?php

Yii::app()->moduleManager->register(array(
    'id' => 'comment',
    'class' => 'application.modules_core.comment.CommentModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.comment.*',
        'application.modules_core.comment.models.*',
        'application.modules_core.comment.notifications.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'User', 'event' => 'onBeforeDelete', 'callback' => array('CommentModule', 'onUserDelete')),
        array('class' => 'HActiveRecordContent', 'event' => 'onBeforeDelete', 'callback' => array('CommentModule', 'onContentDelete')),
        array('class' => 'IntegrityChecker', 'event' => 'onRun', 'callback' => array('CommentModule', 'onIntegrityCheck')),
        array('class' => 'WallEntryLinksWidget', 'event' => 'onInit', 'callback' => array('CommentModule', 'onWallEntryLinksInit')),
        array('class' => 'WallEntryAddonWidget', 'event' => 'onInit', 'callback' => array('CommentModule', 'onWallEntryAddonInit')),
    ),
));
?>