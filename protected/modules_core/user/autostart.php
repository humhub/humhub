<?php

Yii::app()->moduleManager->register(array(
    'id' => 'user',
    'title' => Yii::t('UserModule.base', 'User'),
    'description' => Yii::t('SpaceModule.base', 'Users core'),
    'class' => 'application.modules_core.user.UserModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.user.models.*',
        'application.modules_core.user.widgets.*',
        'application.modules_core.user.forms.*',
        'application.modules_core.user.*',
    ),
    // Events to Catch
    'events' => array(
        array('class' => 'HSearch', 'event' => 'onRebuild', 'callback' => array('UserModule', 'onSearchRebuild')),
    ),
));
?>