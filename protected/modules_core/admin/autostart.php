<?php

Yii::app()->moduleManager->register(array(
    'id' => 'admin',
    'title' => Yii::t('AdminModule.base', 'Admin'),
    'description' => Yii::t('AdminModule.base', 'Provides general admin functions.'),
    'class' => 'application.modules_core.admin.AdminModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.admin.*',
    ),
    'events' => array(
        array('class' => 'DashboardSidebarWidget', 'event' => 'onInit', 'callback' => array('AdminModule', 'onDashboardSidebarInit')),
    ),
));
?>