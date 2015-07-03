<?php

use humhub\core\dashboard\widgets\Sidebar;
use humhub\core\admin\Events;
use humhub\commands\CronController;

return [
    'id' => 'admin',
    'class' => \humhub\core\admin\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        ['class' => Sidebar::className(), 'event' => Sidebar::EVENT_INIT, 'callback' => array(Events::className(), 'onDashboardSidebarInit')],
        ['class' => CronController::className(), 'event' => CronController::EVENT_ON_DAILY_RUN, 'callback' => array(Events::className(), 'onCronDailyRun')],
    ),
];
?>