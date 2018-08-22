<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\admin\Events;
use humhub\commands\CronController;

return [
    'id' => 'admin',
    'class' => \humhub\modules\admin\Module::class,
    'isCoreModule' => true,
    'events' => [
        [
            'class' => Sidebar::class,
            'event' => Sidebar::EVENT_INIT,
            'callback' => [
                Events::class,
                'onDashboardSidebarInit'
            ]
        ],
        [
            'class' => CronController::class,
            'event' => CronController::EVENT_ON_DAILY_RUN,
            'callback' => [
                Events::class,
                'onCronDailyRun'
            ]
        ],
        [
            'class' => 'humhub\components\console\Application',
            'event' => 'onInit',
            'callback' => [
                Events::class,
                'onConsoleApplicationInit'
            ]
        ],
    ],
];
