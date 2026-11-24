<?php

use humhub\commands\CronController;
use humhub\modules\dav\Events;
use humhub\modules\dav\Module;

return [
    'id' => 'dav',
    'class' => Module::class,
    'isCoreModule' => true,
    'urlManagerRules' => [
        // Every method: WebDAV adds its own (PROPFIND, MKCOL, MOVE …).
        ['pattern' => 'dav', 'route' => 'dav/server/index'],
        ['pattern' => 'dav/<path:.*>', 'route' => 'dav/server/index'],
    ],
    'events' => [
        [CronController::class, CronController::EVENT_ON_DAILY_RUN, [Events::class, 'onCronDailyRun']],
    ],
];
