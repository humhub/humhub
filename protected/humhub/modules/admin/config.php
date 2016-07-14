<?php

use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\admin\Events;
use humhub\commands\CronController;
use humhub\components\console\Application;

return [
    'id' => 'admin',
    'class' => \humhub\modules\admin\Module::className(),
    'isCoreModule' => true,
    'events' => array(
        ['class' => Sidebar::className(), 'event' => Sidebar::EVENT_INIT, 'callback' => array(Events::className(), 'onDashboardSidebarInit')],
        ['class' => CronController::className(), 'event' => CronController::EVENT_ON_DAILY_RUN, 'callback' => array(Events::className(), 'onCronDailyRun')],
        ['class' => Application::className(), 'event' => Application::EVENT_ON_INIT, 'callback' => array(Events::className(), 'onConsoleApplicationInit')],
    ),
];
?>