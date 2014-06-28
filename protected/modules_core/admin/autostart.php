<?php

Yii::app()->moduleManager->register(array(
    'id' => 'admin',
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