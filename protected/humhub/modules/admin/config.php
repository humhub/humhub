<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\admin\Events;
use humhub\commands\CronController;
use humhub\components\console\Application;

return [
    'id' => 'admin',
    'class' => \humhub\modules\admin\Module::className(),
    'isCoreModule' => true,
    'events' => [
        [
            'class' => Sidebar::className(),
            'event' => Sidebar::EVENT_INIT,
            'callback' => [
                Events::className(),
                'onDashboardSidebarInit'
            ]
        ],
        [
            'class' => CronController::className(),
            'event' => CronController::EVENT_ON_DAILY_RUN,
            'callback' => [
                Events::className(),
                'onCronDailyRun'
            ]
        ],
        [
            'class' => Application::className(),
            'event' => Application::EVENT_ON_INIT,
            'callback' => [
                Events::className(),
                'onConsoleApplicationInit'
            ]
        ],
    ],
];