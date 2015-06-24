<?php

Yii::app()->moduleManager->register(array(
    'id' => 'admin',
    'class' => 'application.modules_core.admin.AdminModule',
    'isCoreModule' => true,
    'import' => array(
        'application.modules_core.admin.*',
        'application.modules_core.admin.notifications.*',
    ),
    'events' => array(
        array('class' => 'DashboardSidebarWidget', 'event' => 'onInit', 'callback' => array('AdminModuleEvents', 'onDashboardSidebarInit')),
        array('class' => 'ZCronRunner', 'event' => 'onDailyRun', 'callback' => array('AdminModuleEvents', 'onCronDailyRun')),
    ),
));
?>