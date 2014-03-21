<?php

Yii::app()->moduleManager->register(array(
    'id' => 'dashboard',
    'title' => Yii::t('DashboardModule.base', 'Dashboard'),
    'description' => Yii::t('DashboardModule.base', 'Dashboard at main navigation.'),
    'class' => 'application.modules_core.dashboard.DashboardModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.dashboard.*',
    ),
    // Events to Catch 
    'events' => array(
        array('class' => 'TopMenuWidget', 'event' => 'onInit', 'callback' => array('DashboardModule', 'onTopMenuInit')),
    ),
));
?>