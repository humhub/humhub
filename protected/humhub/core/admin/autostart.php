<?php

\Yii::$app->moduleManager->register(array(
    'id' => 'admin',
    'class' => \humhub\core\admin\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        //array('class' => 'DashboardSidebarWidget', 'event' => 'onInit', 'callback' => array('AdminModuleEvents', 'onDashboardSidebarInit')),
        //array('class' => 'ZCronRunner', 'event' => 'onDailyRun', 'callback' => array('AdminModuleEvents', 'onCronDailyRun')),
    ),
));
?>